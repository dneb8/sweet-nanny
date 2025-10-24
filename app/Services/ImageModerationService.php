<?php

namespace App\Services;

use Aws\Rekognition\RekognitionClient;

class ImageModerationService
{
    public function __construct(
        private readonly RekognitionClient $client
    ) {}

    public static function make(): self
    {
        return new self(new RekognitionClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]));
    }

    public function isSafeAndSingleFace(string $bucket, string $key, int $minConfidence = 80): array
    {
        // Moderación
        $mod = $this->client->detectModerationLabels([
            'Image' => ['S3Object' => ['Bucket' => $bucket, 'Name' => $key]],
            'MinConfidence' => $minConfidence,
        ]);

        $ban = [
            'Explicit Nudity','Sexual Activity','Sexual Content',
            'Graphic Male Nudity','Graphic Female Nudity','Violence','Hate Symbols'
        ];

        $blocked = collect($mod['ModerationLabels'] ?? [])
            ->contains(fn($l) => in_array($l['Name'] ?? '', $ban, true)
                && ($l['Confidence'] ?? 0) >= $minConfidence);

        if ($blocked) {
            return [false, 'La imagen no cumple con las políticas de contenido.'];
        }

        // Rostros
        $faces = $this->client->detectFaces([
            'Image' => ['S3Object' => ['Bucket' => $bucket, 'Name' => $key]],
            'Attributes' => ['DEFAULT'],
        ]);

        $count = count($faces['FaceDetails'] ?? []);
        if ($count !== 1) {
            return [false, 'La foto debe mostrar exactamente un rostro.'];
        }

        return [true, null];
    }
}
