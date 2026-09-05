<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Platform-staff roles only (super_admin, support). Regular SaaS
     * customers never get one of these — their access is entirely via
     * TeamMember roles (owner/manager/agent) scoped to a business.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::SUPER_ADMIN,
                'label' => 'Super Admin',
                'description' => 'Full platform access: manage all businesses, users, and settings.',
                'is_system' => true,
                'permissions' => Permission::pluck('name')->all(),
            ],
            [
                'name' => Role::SUPPORT,
                'label' => 'Support',
                'description' => 'Read-only visibility into businesses/users to help with support requests.',
                'is_system' => true,
                'permissions' => ['users.view', 'audit_logs.view'],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissionNames = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::updateOrCreate(['name' => $roleData['name']], $roleData);

            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}
