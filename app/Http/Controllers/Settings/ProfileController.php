<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Jobs\ValidateAvatarMedia;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     * - Lanza validación en background si hay un avatar pendiente.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user()->loadMissing([
            'media' => fn ($q) => $q->where('collection_name', 'images'),
        ]);

        // dispara validación asíncrona si es necesaria
        $this->kickoffAvatarValidationIfNeeded($user);

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'avatarUrl' => $user->avatar_url,     // accessor
            'avatarStatus' => $user->avatar_status,  // accessor
            'avatarNote' => $user->avatar_note,    // accessor
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
     * Update the user's avatar image:
     * - Guarda primero
     * - Redirige de inmediato
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'], // 4MB
        ]);

        $user = $request->user();

        // Guarda la imagen de inmediato (collection 'images' en disco 's3')
        $user->addMediaFromRequest('avatar')
            ->withCustomProperties([
                'status' => 'pending',
                'note' => 'En validación',
            ])
            ->toMediaCollection('images', 's3');

        return to_route('profile.edit')->with('info', 'Tu imagen se subió. Te notificaremos cuando esté validada.');
    }

    /**
     * Delete the user's avatar image.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
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

    // ============================================================
    // Helpers privados
    // ============================================================

    /**
     * Si el usuario tiene media 'images' con status 'pending',
     * dispara el Job de validación (una sola vez por ventana de tiempo).
     */
    private function kickoffAvatarValidationIfNeeded($user): void
    {
        $media = $user->getFirstMedia('images');
        if (! $media) {
            return;
        }

        $status = $media->getCustomProperty('status', 'approved');
        if ($status !== 'pending') {
            return;
        }

        // Evita re-encolar spams (lock de 30s; ajusta a lo que prefieras)
        $lockKey = "validate-avatar:{$user->id}:{$media->id}";
        $lockDurationSeconds = 30; // Prevents re-queuing validation jobs for 30 seconds
        $gotLock = Cache::lock($lockKey, $lockDurationSeconds)->get(); // true si obtiene el lock

        if (! $gotLock) {
            return;
        }

        // Encola validación en background
        ValidateAvatarMedia::dispatch($user->id, $media->id)->onQueue('default');
    }
}
