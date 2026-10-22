<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the fixed catalog of roles and permissions.
 *
 * This seeder is designed to run on EVERY software update of real customer
 * installations (see docs/development-backlog.md F1.3). It must therefore be
 * fully idempotent:
 *
 * - Permissions and roles are created with firstOrCreate(), never duplicated.
 * - A role's permission SET is replaced via syncPermissions() to reflect the
 *   current definition below (so new permissions added in a future release
 *   reach existing roles automatically), but this never touches which USERS
 *   have which roles (model_has_roles is untouched).
 * - Re-running this seeder any number of times leaves existing user → role
 *   assignments intact.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permissions grouped by module, using the `module.action` pattern.
     *
     * @var list<string>
     */
    private const PERMISSIONS = [
        'clients.view',
        'clients.create',
        'clients.update',
        'clients.delete',

        'projects.view',
        'projects.create',
        'projects.update',
        'projects.delete',

        'receipts.view',
        'receipts.import',
        'receipts.review',

        'settings.view',
        'settings.update',

        'users.view',
        'users.create',
        'users.update',
        'users.delete',
    ];

    /**
     * Permission set per role.
     *
     * @var array<string, list<string>>
     */
    private const ROLE_PERMISSIONS = [
        // Owner: full access, including deleting users.
        'owner' => self::PERMISSIONS,

        // Admin: full access except deleting users.
        'admin' => [
            'clients.view', 'clients.create', 'clients.update', 'clients.delete',
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'receipts.view', 'receipts.import', 'receipts.review',
            'settings.view', 'settings.update',
            'users.view', 'users.create', 'users.update',
        ],

        // Engineer: broad read access, can advance projects and receipts.
        'engineer' => [
            'clients.view',
            'projects.view', 'projects.update',
            'receipts.view', 'receipts.import', 'receipts.review',
            'settings.view',
        ],

        // Sales: full control of clients and projects, can import receipts.
        'sales' => [
            'clients.view', 'clients.create', 'clients.update', 'clients.delete',
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'receipts.view', 'receipts.import',
            'settings.view',
        ],

        // Viewer: read-only across every module.
        'viewer' => [
            'clients.view',
            'projects.view',
            'receipts.view',
            'settings.view',
            'users.view',
        ],
    ];

    /**
     * Seed roles and permissions.
     */
    public function run(): void
    {
        // Permission caching can serve stale results while this seeder runs.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            // Replaces the role's permission set to match the current
            // definition without touching which users hold this role.
            $role->syncPermissions($permissions);
        }

        Cache::forget(config('permission.cache.key'));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
