<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Traits\HandlesAvatarValidation;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    use HandlesAvatarValidation;
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
            'avatarUrl' => $user->avatar_url,    
            'avatarStatus' => $user->avatar_status, 
            'avatarNote' => $user->avatar_note,   
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
    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Guarda la imagen de inmediato (collection 'images' en disco 's3')
        $user->addMediaFromRequest('avatar')
            ->withCustomProperties([
                'status' => 'pending',
                'note' => 'En validación',
            ])
            ->toMediaCollection('images', 's3');

        // Trigger validation if needed
        $this->kickoffAvatarValidationIfNeeded($user);

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
}
