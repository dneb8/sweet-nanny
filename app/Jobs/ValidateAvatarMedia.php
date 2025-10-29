<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\AvatarProcessed;
use Aws\Exception\AwsException;
use Aws\Rekognition\RekognitionClient;
use Aws\S3\S3Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidateAvatarMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(
        public int $userId,
        public int $mediaId,
        public int $minConfidence = 80
    ) {
        // Evita colisiones: usa el método del trait
        $this->afterCommit();
    }

    public function handle(): void
    {
        $user = User::findOrFail($this->userId);

        $media = $user->media()->whereKey($this->mediaId)->first();
        if (!$media) {
            return;
        }

        $bucket = config('filesystems.disks.s3.bucket');
        $region = config('filesystems.disks.s3.region', env('AWS_DEFAULT_REGION', 'us-east-2'));
        $key    = ltrim($media->getPathRelativeToRoot(), '/');

        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $rekognition = new RekognitionClient([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        try {
            // 1) Espera breve: si el objeto no está listo, reencola y sal
            if (!$this->waitForS3Object($s3, $bucket, $key)) {
                $this->release(10);
                return;
            }

            // 2) Moderación (con un reintento breve si hay timing de metadata)
            $mod = $this->retryRekognition(fn () => $rekognition->detectModerationLabels([
                'Image'         => ['S3Object' => ['Bucket' => $bucket, 'Name' => $key]],
                'MinConfidence' => $this->minConfidence,
            ]));

            $ban = [
                'Explicit Nudity','Sexual Activity','Sexual Content',
                'Graphic Male Nudity','Graphic Female Nudity','Violence','Hate Symbols',
            ];

            $blocked = collect($mod['ModerationLabels'] ?? [])->contains(
                fn ($l) => in_array($l['Name'] ?? '', $ban, true)
                    && ($l['Confidence'] ?? 0) >= $this->minConfidence
            );

            if ($blocked) {
                $media->delete();
                $user->notify(new AvatarProcessed(false, 'La imagen no cumple con las políticas de contenido.'));
                return;
            }

            // 3) Rostros (mismo patrón de reintento corto)
            $faces = $this->retryRekognition(fn () => $rekognition->detectFaces([
                'Image'      => ['S3Object' => ['Bucket' => $bucket, 'Name' => $key]],
                'Attributes' => ['DEFAULT'],
            ]));

            $faceCount = count($faces['FaceDetails'] ?? []);
            if ($faceCount !== 1) {
                $media->delete();
                $msg = $faceCount === 0
                    ? 'Tu imagen fue rechazada porque no se detectó ningún rostro.'
                    : 'Tu imagen fue rechazada porque se detectaron múltiples rostros. Solo debe haber uno.';
                $user->notify(new AvatarProcessed(false, $msg));
                return;
            }

            // 4) Aprobado
            $media->setCustomProperty('status', 'approved');
            $media->setCustomProperty('note', 'Validada');
            $media->save();

            $user->notify(new AvatarProcessed(true, '¡Tu foto de perfil ha sido aprobada!'));

        } catch (AwsException $e) {
            // Errores AWS (permisos/región/red). Reintento simple.
            Log::warning('ValidateAvatarMedia AWS', [
                'code'    => $e->getAwsErrorCode(),
                'message' => $e->getAwsErrorMessage() ?: $e->getMessage(),
                'bucket'  => $bucket,
                'key'     => $key,
            ]);
            $this->release(10);
        } catch (\Throwable $e) {
            // Cualquier otro error transitorio
            Log::error('ValidateAvatarMedia error', [
                'user_id' => $this->userId,
                'media_id'=> $this->mediaId,
                'message' => $e->getMessage(),
            ]);
            $this->release(10);
        }
    }

    /**
     * Espera a que el objeto exista en S3. Devuelve true si aparece.
     */
    protected function waitForS3Object(S3Client $s3, string $bucket, string $key, int $maxAttempts = 6, int $sleepMs = 250): bool
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            try {
                $s3->headObject(['Bucket' => $bucket, 'Key' => $key]);
                return true;
            } catch (AwsException $e) {
                usleep($sleepMs * 1000);
            }
        }
        return false;
    }

    /**
     * Llama a Rekognition con 1 reintento frente a InvalidS3ObjectException.
     */
    protected function retryRekognition(callable $fn)
    {
        for ($i = 0; $i < 2; $i++) {
            try {
                return $fn();
            } catch (AwsException $e) {
                $msg = $e->getAwsErrorCode() ?: $e->getMessage();
                if ($i === 0 && (
                    stripos($msg, 'InvalidS3ObjectException') !== false ||
                    stripos($e->getAwsErrorMessage() ?? '', 'Unable to get object metadata') !== false
                )) {
                    usleep(400 * 1000);
                    continue;
                }
                throw $e;
            }
        }
        // Último intento
        return $fn();
    }
}
