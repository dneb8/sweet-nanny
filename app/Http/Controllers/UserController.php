<?php

namespace App\Http\Controllers;

use App\Enums\User\RoleEnum;
use App\Http\Requests\User\{CreateUserRequest, UpdateUserRequest};
use App\Models\{User};
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Inertia\{Inertia, Response};

class UserController extends Controller
{
    /**
     * Redirige al listado de usuarios
     */
    public function index(UserService $userService): Response
    {
        // Gate::authorize('viewAny', User::class);

        $sortables = ['role', 'email_verified_at'];
        $searchables = ['name', 'email', 'surnames'];
        $users = $userService->indexFetch();

        return Inertia::render('User/Index', [
            'users' => $users,
            'roles' => RoleEnum::cases(),
            'sortables' => $sortables,
            'searchables' => $searchables,
        ]);
    }

    /**
     * Redirige a la página para crear un usuario
     */
    public function create(): Response
    {
        // Gate::authorize('create', User::class);
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
        ]);
    }

    /**
     * Redirige a la página para editar un usuario
     */
    public function edit(User $user): Response
    {
        // Gate::authorize('update', $user);
        
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
        // Gate::authorize('update', $user);

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
        if ($user->hasRole('nanny')) {
            return redirect()->route('nannies.show', $user->nanny);
        }

        if ($user->hasRole('tutor')) {
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
        // Gate::authorize('delete', $user);

        User::destroy($user->id);

        return redirect()->back()->with([
            'message' => [
                'title' => 'Usuario eliminado',
                'description' => 'El usuario ha sido eliminado correctamente.',
            ]
        ]);
    }
}
