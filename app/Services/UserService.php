<?php

namespace App\Services;

use App\Classes\Fetcher\{Fetcher, Filter};
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

        $users = User::query();

        $sortables = ['email'];
        $searchables = ['name, email'];

        $users = Fetcher::for($users->whereNot('id', $user->id)->with(['roles']))
            ->allowSort($sortables)
            ->allowSearch($searchables)
            ->paginate(User::whereNot('id', Auth::id())->count());

        return $users;
    }

    /**
     * Elimina un usuario
     */
    public function eliminarUsuario(User $user): void
    {
        User::destroy($user->id);
    }
}
