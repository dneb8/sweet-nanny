<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\AvatarProcessed;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessAvatarUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $tmpKey,
        public int $minConfidence = 80
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $bucket = 'sweet-nanny';

        // Initialize Rekognition client
        $rekognition = new RekognitionClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION', 'us-east-2'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        try {
            // Step 1: Check for inappropriate content with detectModerationLabels
            $moderation = $rekognition->detectModerationLabels([
                'Image' => [
                    'S3Object' => [
                        'Bucket' => $bucket,
                        'Name' => $this->tmpKey,
                    ],
                ],
                'MinConfidence' => $this->minConfidence,
            ]);

            // Block specific inappropriate labels
            $bannedLabels = [
                'Explicit Nudity',
                'Sexual Activity',
                'Sexual Content',
                'Graphic Male Nudity',
                'Graphic Female Nudity',
                'Violence',
                'Hate Symbols',
            ];

            foreach ($moderation['ModerationLabels'] ?? [] as $label) {
                if (in_array($label['Name'] ?? '', $bannedLabels, true) 
                    && ($label['Confidence'] ?? 0) >= $this->minConfidence) {
                    // Reject: inappropriate content
                    $this->user->notify(new AvatarProcessed(
                        success: false,
                        message: 'Tu imagen fue rechazada por contener contenido inapropiado.'
                    ));

                    // Delete temporary file
                    Storage::disk('s3')->delete($this->tmpKey);
                    return;
                }
            }

            // Step 2: Check for exactly 1 face with detectFaces
            $faces = $rekognition->detectFaces([
                'Image' => [
                    'S3Object' => [
                        'Bucket' => $bucket,
                        'Name' => $this->tmpKey,
                    ],
                ],
                'Attributes' => ['DEFAULT'],
            ]);

            $faceCount = count($faces['FaceDetails'] ?? []);

            if ($faceCount !== 1) {
                // Reject: must have exactly 1 face
                $message = $faceCount === 0
                    ? 'Tu imagen fue rechazada porque no se detectó ningún rostro.'
                    : 'Tu imagen fue rechazada porque se detectaron múltiples rostros. Solo debe haber uno.';

                $this->user->notify(new AvatarProcessed(
                    success: false,
                    message: $message
                ));

                // Delete temporary file
                Storage::disk('s3')->delete($this->tmpKey);
                return;
            }

            // Step 3: Image passed validation! Move to Spatie Media Library
            // Download from temp location
            $contents = Storage::disk('s3')->get($this->tmpKey);

            // Save locally temporarily to add via Spatie
            $localTmpPath = storage_path('app/tmp/' . basename($this->tmpKey));
            @mkdir(dirname($localTmpPath), 0755, true);
            file_put_contents($localTmpPath, $contents);

            // Clear existing avatar if any (singleFile behavior)
            $this->user->clearMediaCollection('images');

            // Add to Spatie Media Library
            $this->user->addMedia($localTmpPath)
                ->toMediaCollection('images', 's3');

            // Clean up
            Storage::disk('s3')->delete($this->tmpKey);
            @unlink($localTmpPath);

            // Success notification
            $this->user->notify(new AvatarProcessed(
                success: true,
                message: '¡Tu foto de perfil ha sido actualizada exitosamente!'
            ));

        } catch (\Exception $e) {
            // Handle any errors
            $this->user->notify(new AvatarProcessed(
                success: false,
                message: 'Ocurrió un error al procesar tu imagen. Por favor, intenta nuevamente.'
            ));

            // Clean up temporary file
            Storage::disk('s3')->delete($this->tmpKey);

            // Optionally re-throw to mark job as failed
            throw $e;
        }
    }
}
