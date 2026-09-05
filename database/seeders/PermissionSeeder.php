<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Platform-level permissions only (for super_admin/support staff who
     * operate the SaaS itself). Per-business actions (managing orders,
     * team members, channels) are governed by TeamMember::role instead —
     * see EnsureBusinessRole middleware.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'users.view', 'label' => 'View users', 'group' => 'users'],
            ['name' => 'users.manage', 'label' => 'Create/edit/disable users', 'group' => 'users'],
            ['name' => 'users.delete', 'label' => 'Delete users', 'group' => 'users'],
            ['name' => 'roles.manage', 'label' => 'Manage roles & permissions', 'group' => 'users'],
            ['name' => 'settings.manage', 'label' => 'Manage system-wide settings', 'group' => 'settings'],
            ['name' => 'audit_logs.view', 'label' => 'View audit logs', 'group' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
