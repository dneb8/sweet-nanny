<?php

namespace App\Services;

use App\Classes\Fetcher\Filter;
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
        $user = Auth::user();
        $request = request();

        $query = User::query()
            ->whereNot('id', $user->id)
            ->with(['roles', 'tutor', 'nanny.qualities']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('surnames', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($filters = $request->get('filters')) {
            // Role filter
            if (! empty($filters['role'])) {
                $query->whereHas('roles', function ($q) use ($filters) {
                    $q->where('name', $filters['role']);
                });
            }

            // Status filter (assuming active/inactive based on deleted_at if using soft deletes)
            if (! empty($filters['status'])) {
                if ($filters['status'] === 'active') {
                    // Active users (not soft deleted)
                    $query->whereNull('deleted_at');
                } elseif ($filters['status'] === 'inactive') {
                    // Inactive users (soft deleted)
                    $query->onlyTrashed();
                }
            }

            // Email verified filter
            if (! empty($filters['verified'])) {
                if ($filters['verified'] === '1' || $filters['verified'] === 'verified') {
                    $query->whereNotNull('email_verified_at');
                } elseif ($filters['verified'] === '0' || $filters['verified'] === 'unverified') {
                    $query->whereNull('email_verified_at');
                }
            }
        }

        // Sorting with whitelist
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('dir', 'desc');

        $allowedSortFields = ['name', 'email', 'created_at', 'email_verified_at'];
        $allowedDirections = ['asc', 'desc'];

        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        if (! in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'desc';
        }

        // Special handling for role sorting
        if ($request->get('sort') === 'role') {
            $query->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('users.*')
                ->distinct()
                ->orderByRaw('COALESCE(roles.name, "") '.$sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        if ($perPage === 'all') {
            $perPage = $query->count();
        } else {
            $perPage = min((int) $perPage, 100); // Max 100 items per page
        }

        return $query->paginate($perPage)->appends($request->query());
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
