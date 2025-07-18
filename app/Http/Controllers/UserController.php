<?php

namespace App\Http\Controllers;

use App\Enums\User\RoleEnum;
use App\Http\Requests\User\{ActualizarUserRequest, CrearUserRequest};
use App\Models\{User, Persona};
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\{Inertia, Response};

class UserController extends Controller
{
    /**
     * Redirige al listado de usuarios
     */
    public function index(UserService $userService): Response
    {
        // Gate::authorize('viewAny', User::class);

        $users = $userService->indexFetch();

        return Inertia::render('User/Index', [
            'users' => $users,
            'roles' => RoleEnum::cases(),
        ]);
    }

//     /**
//      * Redirige a la página para crear un usuario
//      */
//     public function create(): Response
//     {
//         // Gate::authorize('create', User::class);
// 
//     }

//     /**
//      * Crea un usuario
//      */
//     public function store(UserService $userService, CrearUserRequest $request): RedirectResponse
//     {
//         // Gate::authorize('create', User::class);
//     }
// 
//     /**
//      * Redirige a la página para editar un usuario
//      */
//     public function edit(User $user): Response
//     {
//         // Gate::authorize('update', $user);
//     }
// 
//     /**
//      * Actualiza un usuario
//      */
//     public function update(UserService $userService, ActualizarUserRequest $request, User $user): RedirectResponse
//     {
//         // Gate::authorize('update', $user);
//     }
// 
//     /**
//      * Elimina un usuario
//      */
//     public function destroy(UserService $userService, User $user): RedirectResponse
//     {
//         // Gate::authorize('delete', $user);
//     }
}
