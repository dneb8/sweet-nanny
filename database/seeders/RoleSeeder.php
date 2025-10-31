<?php

namespace Database\Seeders;

use App\Enums\Permissions\UserPermission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $nannyRole = Role::firstOrCreate(['name' => 'nanny']);
        $tutorRole = Role::firstOrCreate(['name' => 'tutor']);

        // Create permissions from UserPermission enum
        $permissions = [];
        foreach (UserPermission::cases() as $permission) {
            $permissions[] = Permission::firstOrCreate(['name' => $permission->value]);
        }

        // Sync permissions to admin role based on UserPermission map
        $adminPermissions = [];
        foreach (UserPermission::cases() as $permission) {
            $allowedRoles = UserPermission::rolesFor($permission->value);
            if (in_array(\App\Enums\User\RoleEnum::ADMIN, $allowedRoles, true)) {
                $adminPermissions[] = $permission->value;
            }
        }

        $adminRole->syncPermissions($adminPermissions);
        $nannyRole->syncPermissions([]); // No permissions for nanny
        $tutorRole->syncPermissions([]); // No permissions for tutor
    }
}
