<?php

namespace App\Services;

use App\Classes\Fetcher\{Fetcher, Filter};
use App\Enums\User\RoleEnum;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Obtener todos los usuarios en el formato que se requiere para el componente DataTable
     */
    public function indexFetch(): LengthAwarePaginator
    {
        $users = User::query()
            ->with([
                'roles',
                'nanny',
                'nanny.qualities',
                'tutor',
                // 'profilePhoto',
            ])
            ->orderBy('created_at', 'desc');

        $sortables = ['email','name','surnames'];
        $searchables = ['email', 'name', 'surnames'];

        $users = Fetcher::for($users)
            ->allowFilters([
                'role' => [
                    'using' => fn (Filter $filter) => $filter->usingScope('filtrarPorRole'),
                ],
            ])
            ->allowSort($sortables)
            ->allowSearch($searchables)
            ->paginate(12);

        return $users;
    }

    /**
     * Crea un usuario
     */
    public function createUser(CreateUserRequest $request): User
    {
        $validated = $request->safe();

        $user = User::create([
            'name' => $validated->name,
            'surnames' => $validated->surnames,
            'email' => $validated->email,
            'number' => $validated->number,
            'password' => bcrypt(Str::random(10)),
        ]);

        $role = RoleEnum::from($validated->roles);

        $user->assignRole($role);

        // Crear relación dependiendo del rol
        if ($role === RoleEnum::NANNY) {
            $user->nanny()->create([]);
        }

        if ($role === RoleEnum::TUTOR) {
            $user->tutor()->create([]);
        }

        return $user;
    }

    public function updateUser(User $user, UpdateUserRequest $request): void
    {
        $validated = $request->safe();

        $user->update([
            'name' => $validated->name,
            'surnames' => $validated->surnames,
            'email' => $validated->email,
            'number' => $validated->number,
        ]);

        $user->syncRoles(RoleEnum::from($validated->roles));
    }
}
