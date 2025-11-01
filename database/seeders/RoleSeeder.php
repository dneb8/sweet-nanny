<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Ajusta estos namespaces a los tuyos reales:
use App\Enums\User\RoleEnum as UserRoleEnumA;               // si usas App\Enums\User\RoleEnum
use App\Enums\Role\RoleEnum as UserRoleEnumB;               // si usas App\Enums\Role\RoleEnum
use App\Enums\Permissions\UserPermission;
use App\Enums\Permissions\BookingAppointmentPermission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        // === 1) Resolver tu RoleEnum real (User\RoleEnum o Role\RoleEnum) ===
        $RoleEnum = class_exists(UserRoleEnumA::class) ? UserRoleEnumA::class : UserRoleEnumB::class;

        // === 2) Crear roles con guard correcto ===
        foreach ($RoleEnum::cases() as $roleEnum) {
            Role::firstOrCreate([
                'name'       => $roleEnum->value,
                'guard_name' => $guard,
            ]);
        }

        // === 3) Define los enums de permisos que quieres sincronizar ===
        $permissionEnums = [
            UserPermission::class,
            BookingAppointmentPermission::class, // agrega más enums si tienes
        ];

        // === 4) Por cada enum: crear permisos y asignarlos a roles según map() ===
        foreach ($permissionEnums as $enumClass) {
            // map(): ['perm.name' => [RoleEnum::ADMIN, RoleEnum::TUTOR], ...]
            $map = $enumClass::map();

            foreach ($map as $permName => $allowedRoleEnums) {
                // Crear/asegurar el permiso en el guard correcto
                Permission::firstOrCreate([
                    'name'       => $permName,
                    'guard_name' => $guard,
                ]);

                // Asignar el permiso a cada rol permitido
                foreach ($allowedRoleEnums as $allowedRoleEnum) {
                    $role = Role::where('name', $allowedRoleEnum->value)
                        ->where('guard_name', $guard)
                        ->first();

                    if ($role) {
                        $role->givePermissionTo($permName);
                    }
                }
            }
        }

        // === 5) Limpiar la caché de Spatie ===
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
