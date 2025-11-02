<?php

namespace App\Http\Controllers;

use App\Enums\User\RoleEnum;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Traits\HandlesAvatarValidation;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    use HandlesAvatarValidation;
    /**
     * Redirige al listado de usuarios
     */
    public function index(UserService $userService): Response
    {
        Gate::authorize('viewAny', User::class);

        $roles = array_map(fn ($role) => $role->value, RoleEnum::cases());

        $users = $userService->indexFetch();

        return Inertia::render('User/Index', [
            'users' => $users,
            'roles' => array_values($roles),
        ]);
    }

    /**
     * Redirige a la página para crear un usuario
     */
    public function create(): Response
    {
        Gate::authorize('create', User::class);
        $roles = RoleEnum::cases();

        return Inertia::render('User/Create', [
            'roles' => $roles,
        ]);

    }

    /**
     * Crea un usuario
     */
    public function store(UserService $userService, CreateUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);
        
        $user = $userService->createUser($request);

        if ($user->hasRole(RoleEnum::NANNY->value)) {
            return redirect()->route('nannies.show', $user->nanny)
                ->with('message', [
                    'title' => 'Usuario creado',
                    'description' => 'La niñera ha sido creada correctamente.',
                ]);
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            return redirect()->route('tutors.show', $user->tutor)
                ->with('message', [
                    'title' => 'Usuario creado',
                    'description' => 'El tutor ha sido creado correctamente.',
                ]);
        }

        return redirect()->route('users.index')->with('message', [
            'title' => 'Usuario creado',
            'description' => 'El usuario ha sido creado correctamente.',
            'icon' => 'check',
        ]);
    }

    /**
     * Redirige a la página para editar un usuario
     */
    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('User/Edit', [
            'user' => $user->load(['roles']),
            'roles' => RoleEnum::cases(),
        ]);
    }

    /**
     * Actualiza un usuario
     */
    public function update(UserService $userService, UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $userService->updateUser($user, $request);

        return redirect()->route('users.index')->with([
            'message' => [
                'title' => 'Usuario actualizado',
                'description' => 'El usuario ha sido actualizado correctamente.',
            ],
        ]);
    }

    /**
     * Muestra el perfil del usuario según su rol
     */
    public function show(User $user)
    {
        Gate::authorize('viewAny', User::class);
        
        if ($user->hasRole(RoleEnum::NANNY->value)) {
            return redirect()->route('nannies.show', $user->nanny);
        }

        if ($user->hasRole(RoleEnum::TUTOR->value)) {
            return redirect()->route('tutors.show', $user->tutor);
        }

        return Inertia::render('User/Show', [
            'user' => $user->load(['roles']),
        ]);
    }

    /**
     * Elimina un usuario
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        User::destroy($user->id);

        return redirect()->back()->with([
            'message' => [
                'title' => 'Usuario eliminado',
                'description' => 'El usuario ha sido eliminado correctamente.',
            ],
        ]);
    }

    /**
     * Update a user's avatar image.
     * Allows the user themselves or an admin to upload an avatar.
     */
    public function updateAvatar(Request $request, User $user): RedirectResponse
    {
        // Authorization: only the user themselves or admin can update avatar
        if (Auth::id() !== $user->id && ! Auth::user()?->hasRole('admin')) {
            abort(403, 'No tienes permiso para actualizar el avatar de este usuario.');
        }

        // Validate the avatar
        $request->validate([
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:4096', // 4MB in KB
            ],
        ]);

        // Save the image immediately (collection 'images' on disk 's3')
        $user->addMediaFromRequest('avatar')
            ->withCustomProperties([
                'status' => 'pending',
                'note' => 'En validación',
            ])
            ->toMediaCollection('images', 's3');

        // Trigger validation if needed
        $this->kickoffAvatarValidationIfNeeded($user);

        return redirect()->back()->with('info', 'Tu imagen se subió. Te notificaremos cuando esté validada.');
    }

    /**
     * Delete a user's avatar image.
     * Allows the user themselves or an admin to delete an avatar.
     */
    public function deleteAvatar(Request $request, User $user): RedirectResponse
    {
        // Authorization: only the user themselves or admin can delete avatar
        if (Auth::id() !== $user->id && ! Auth::user()?->hasRole('admin')) {
            abort(403, 'No tienes permiso para eliminar el avatar de este usuario.');
        }

        $user->clearMediaCollection('images');

        return redirect()->back()->with('success', 'Foto de perfil eliminada correctamente.');
    }
}
