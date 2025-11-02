<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure required roles exist for guard 'web' before any test runs
        $this->ensureRolesExist(['admin', 'nanny', 'tutor']);

        // Clear Spatie's permission cache to avoid stale state
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Ensure roles exist for the web guard (idempotent)
     */
    protected function ensureRolesExist(array $roleNames): void
    {
        foreach ($roleNames as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }
    }
}
