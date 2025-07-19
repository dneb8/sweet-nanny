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

        $users = Fetcher::for($users->whereNot('id', $user->id)->with(['roles']))
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
