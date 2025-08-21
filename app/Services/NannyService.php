<?php

namespace App\Services;

use App\Classes\Fetcher\{Fetcher, Filter};
use App\Enums\User\RoleEnum;
use App\Http\Requests\Nanny\CreateNannyRequest;
use App\Http\Requests\Nanny\UpdateNannyRequest;
use App\Models\{Nanny};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, Validator};
use Illuminate\Support\Str;

class NannyService
{
    /**
     * Obtener todos los usuarios en el formato que se requiere para el componente DataTable
     */
    public function indexFetch(): LengthAwarePaginator
    {

        $nannies = Nanny::query()->orderBy('created_at', 'desc');

        $nannies = Fetcher::for(
            $nannies->with(['user.roles', 'courses', 'careers', 'qualities', 'reviews'])
        )->paginate();

        return $nannies;
    }

    /**
     * Crea un usuario
     */
    public function createNanny(CreateNannyRequest $request): void
    {
        $validated = $request->safe();

        $nanny = Nanny::create([
            'name' => $validated->name,
            'surnames' => $validated->surnames,
            'email' => $validated->email,
            'number' => $validated->number,
            'password' => bcrypt(Str::random(10)),
        ]);

    $nanny->assignRole(RoleEnum::from($validated->roles));

    }

    public function updateNanny(Nanny $nanny, UpdateNannyRequest $request): void
    {
        $validated = $request->safe();

        $nanny->update([
            'name' => $validated->name,
            'surnames' => $validated->surnames,
            'email' => $validated->email,
            'number' => $validated->number,
        ]);

        $nanny->syncRoles(RoleEnum::from($validated->roles));   
    }
}
