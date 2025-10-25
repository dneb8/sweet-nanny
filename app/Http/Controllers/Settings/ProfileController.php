<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Jobs\ProcessAvatarUpload;
use App\Jobs\ValidateAvatarMedia;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status'          => $request->session()->get('status'),
            'avatarUrl'       => $user->avatar_url,
            'avatarStatus'    => $user->avatar_status,
            'avatarNote'      => $user->avatar_note,
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
     * Update the user's avatar image (save first, validate after).
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'], // 4MB
        ]);

        $user = $request->user();

        // 1) Save image immediately to Spatie (S3 collection 'images')
        $media = $user->addMediaFromRequest('avatar')
            ->withCustomProperties([
                'status' => 'pending',
                'note' => 'En validación',
            ])
            ->toMediaCollection('images', 's3'); // singleFile() replaces previous

        // 2) Dispatch background validation job
        ValidateAvatarMedia::dispatch($user->id, $media->id)->onQueue('default');

        // 3) Immediate response
        return to_route('profile.edit')->with('info', 'Tu imagen se subió. Te notificaremos cuando esté validada.');
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
