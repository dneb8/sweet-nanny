<?php

namespace App\Http\Controllers;

use App\Enums\User\RoleEnum;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Redirige al listado de usuarios
     */
    public function index(UserService $userService): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('viewAny', User::class);

        $availableRoles = array_map(fn ($role) => $role->value, RoleEnum::cases());

        $users = $userService->indexFetch();

        return Inertia::render('User/Index', [
            'users' => $users,
            'roles' => array_values($availableRoles),
        ]);
    }

    /**
     * Redirige a la página para crear un usuario
     */
    public function create(): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('create', User::class);
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
        \Illuminate\Support\Facades\Gate::authorize('update', $user);

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
        \Illuminate\Support\Facades\Gate::authorize('update', $user);

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
        \Illuminate\Support\Facades\Gate::authorize('delete', $user);

        User::destroy($user->id);

        return redirect()->back()->with([
            'message' => [
                'title' => 'Usuario eliminado',
                'description' => 'El usuario ha sido eliminado correctamente.',
            ],
        ]);
    }
}
