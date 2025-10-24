<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Services\ImageModerationService;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $avatarUrl = $user->getFirstMediaUrl('images');

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'avatarUrl' => $avatarUrl ?: null,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit')->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Update the user's avatar image.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'], // 4MB
        ]);

        // 1) Lee el archivo a memoria (bytes)
        $file = $request->file('avatar');
        $bytes = file_get_contents($file->getRealPath());

        // 2) Cliente Rekognition (misma región que tu bucket)
        $rekognition = new RekognitionClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'), // ej. us-east-2
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        // 3) Moderación rápida (bloquea contenido no apto)
        $mod = $rekognition->detectModerationLabels([
            'Image' => ['Bytes' => $bytes],
            'MinConfidence' => 80, // ajusta si quieres
        ]);

        $ban = [
            'Explicit Nudity','Sexual Activity','Sexual Content',
            'Graphic Male Nudity','Graphic Female Nudity',
            'Violence','Hate Symbols'
        ];

        $blocked = collect($mod['ModerationLabels'] ?? [])
            ->contains(fn($l) =>
                in_array($l['Name'] ?? '', $ban, true) &&
                ($l['Confidence'] ?? 0) >= 80
            );

        if ($blocked) {
            return to_route('profile.edit')
                ->withErrors(['avatar' => 'La imagen no cumple con las políticas de contenido.']);
        }

        // 4) Rostros: exige exactamente 1
        $faces = $rekognition->detectFaces([
            'Image' => ['Bytes' => $bytes],
            'Attributes' => ['DEFAULT'],
        ]);

        if (count($faces['FaceDetails'] ?? []) !== 1) {
            return to_route('profile.edit')
                ->withErrors(['avatar' => 'La foto debe mostrar exactamente un rostro.']);
        }

        // 5) Si pasa → ahora sí guarda en Spatie (S3)
        $request->user()
            ->addMediaFromRequest('avatar') // usa el mismo archivo del request
            ->toMediaCollection('images', 's3');

        return to_route('profile.edit')->with('success', 'Foto de perfil actualizada correctamente.');
    }

    /**
     * Delete the user's avatar image.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Clear all images from the 'images' collection
        $user->clearMediaCollection('images');

        return to_route('profile.edit')->with('success', 'Foto de perfil eliminada correctamente.');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
