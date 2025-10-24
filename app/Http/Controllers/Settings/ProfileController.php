<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Jobs\ProcessAvatarUpload;
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
     * Update the user's avatar image (async with background validation).
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'], // 4MB
        ]);

        $user = $request->user();
        $file = $request->file('avatar');

        // Generate unique temporary key for S3 storage
        $tmpKey = 'tmp/avatars/' . $user->ulid . '/' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Upload to S3 temporary location
        Storage::disk('s3')->put($tmpKey, file_get_contents($file->getRealPath()));

        // Dispatch background job for validation
        ProcessAvatarUpload::dispatch($user, $tmpKey);

        return to_route('profile.edit')->with('info', 'Tu imagen está siendo validada. Recibirás una notificación cuando esté lista.');
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
