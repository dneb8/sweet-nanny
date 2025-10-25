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

class ValidateAvatarMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public int $mediaId,
        public int $minConfidence = 80
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::findOrFail($this->userId);

        $media = $user->media()->whereKey($this->mediaId)->first();
        if (!$media) {
            return;
        }

        // 1) Initialize Rekognition client
        $client = new RekognitionClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION', 'us-east-2'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        // 2) Get S3 bucket and key from Spatie media
        $bucket = config('filesystems.disks.s3.bucket');
        $key = $media->getPathRelativeToRoot(); // S3 key path

        try {
            // 3) Content Moderation Check
            $mod = $client->detectModerationLabels([
                'Image' => ['S3Object' => ['Bucket' => $bucket, 'Name' => $key]],
                'MinConfidence' => $this->minConfidence,
            ]);

            $ban = [
                'Explicit Nudity',
                'Sexual Activity',
                'Sexual Content',
                'Graphic Male Nudity',
                'Graphic Female Nudity',
                'Violence',
                'Hate Symbols',
            ];

            $blocked = collect($mod['ModerationLabels'] ?? [])
                ->contains(fn ($l) => in_array($l['Name'] ?? '', $ban, true)
                    && ($l['Confidence'] ?? 0) >= $this->minConfidence);

            if ($blocked) {
                $media->delete(); // ❌ Delete image from Spatie (and S3)
                $user->notify(new AvatarProcessed(false, 'La imagen no cumple con las políticas de contenido.'));
                return;
            }

            // 4) Face Detection Check (exactly 1 face required)
            $faces = $client->detectFaces([
                'Image' => ['S3Object' => ['Bucket' => $bucket, 'Name' => $key]],
                'Attributes' => ['DEFAULT'],
            ]);

            $faceCount = count($faces['FaceDetails'] ?? []);

            if ($faceCount !== 1) {
                $media->delete(); // ❌ Delete image from Spatie (and S3)
                $message = $faceCount === 0
                    ? 'Tu imagen fue rechazada porque no se detectó ningún rostro.'
                    : 'Tu imagen fue rechazada porque se detectaron múltiples rostros. Solo debe haber uno.';
                $user->notify(new AvatarProcessed(false, $message));
                return;
            }

            // 5) Validation passed! Update custom properties
            $media->setCustomProperty('status', 'approved');
            $media->setCustomProperty('note', 'Validada');
            $media->save();

            $user->notify(new AvatarProcessed(true, '¡Tu foto de perfil ha sido aprobada!'));

        } catch (\Exception $e) {
            // On error, delete the image and notify user
            $media->delete();
            $user->notify(new AvatarProcessed(false, 'Ocurrió un error al procesar tu imagen. Por favor, intenta nuevamente.'));
            throw $e;
        }
    }
}
