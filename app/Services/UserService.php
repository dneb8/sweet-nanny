<?php

namespace App\Services;

use App\Classes\Fetcher\{Fetcher, Filter};
use App\Enums\User\RoleEnum;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\{User};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, Validator};
use Illuminate\Support\Str;

class UserService
{
    /**
     * Obtener todos los usuarios en el formato que se requiere para el componente DataTable
     */
    public function indexFetch(): LengthAwarePaginator
    {
        $user = Auth::user();

        $users = User::query()->orderBy('created_at', 'desc');

        $users = Fetcher::for($users->whereNot('id', $user->id)->with(['roles']))
            ->paginate(User::whereNot('id', Auth::id())->count());

        return $users;
    }

    /**
     * Crea un usuario
     */
    public function createUser(CreateUserRequest $request): void
    {
        $validated = $request->safe();

        $user = User::create([
            'name' => $validated->name,
            'surnames' => $validated->surnames,
            'email' => $validated->email,
            'number' => $validated->number,
            'password' => bcrypt(Str::random(10)),
        ]);

    $user->assignRole(RoleEnum::from($validated->roles));

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
