<?php

namespace App\Services;

use App\Classes\Fetcher\{Fetcher, Filter};
use App\Enums\User\RoleEnum;
use App\Http\Requests\Tutor\CreateTutorRequest;
use App\Http\Requests\Tutor\UpdateTutorRequest;
use App\Models\{Tutor};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, Validator};
use Illuminate\Support\Str;

class TutorService
{
    /**
     * Obtener todos los usuarios en el formato que se requiere para el componente DataTable
     */
    public function indexFetch(): LengthAwarePaginator
    {

        $tutors = Tutor::query()->orderBy('created_at', 'desc');

        $tutors = Fetcher::for(
            $tutors->with(['user.roles', 'courses', 'careers', 'qualities', 'reviews'])
        )->paginate();

        return $tutors;
    }

    /**
     * Crea un usuario
     */
    public function createTutor(CreateTutorRequest $request): void
    {
        $validated = $request->safe();

        $tutor = Tutor::create([
            'name' => $validated->name,
            'surnames' => $validated->surnames,
            'email' => $validated->email,
            'number' => $validated->number,
            'password' => bcrypt(Str::random(10)),
        ]);

    $tutor->assignRole(RoleEnum::from($validated->roles));

    }

    public function updateTutor(Tutor $tutor, UpdateTutorRequest $request): void
    {
        $validated = $request->safe();

        $tutor->update([
            'name' => $validated->name,
            'surnames' => $validated->surnames,
            'email' => $validated->email,
            'number' => $validated->number,
        ]);

        $tutor->syncRoles(RoleEnum::from($validated->roles));   
    }

    /**
     * Get data for showing a tutor profile
     * Eager loads relationships to avoid N+1 queries
     */
    public function getShowData(string $id): Tutor
    {
        return Tutor::where('ulid', $id)
            ->with([
                'user.address',   // user information with address
                'children',       // children information
            ])
            ->firstOrFail();
    }
}
