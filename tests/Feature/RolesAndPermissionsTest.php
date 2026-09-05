<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the PLATFORM-level Role/Permission system (staff who operate the
 * SaaS itself: super_admin/support). This is separate from TeamMember,
 * which governs a regular customer's access within a business — see
 * tests/Feature/Business/TeamManagementTest.php and
 * tests/Feature/Tenancy/TenantIsolationTest.php for that.
 */
class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_super_admin_has_every_permission(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', Role::SUPER_ADMIN)->first());

        $this->assertTrue($admin->hasPermission('users.manage'));
        $this->assertTrue($admin->hasPermission('settings.manage'));
    }

    public function test_support_role_has_only_read_only_permissions(): void
    {
        $support = User::factory()->create();
        $support->roles()->attach(Role::where('name', Role::SUPPORT)->first());

        $this->assertTrue($support->hasPermission('users.view'));
        $this->assertFalse($support->hasPermission('users.manage'));
        $this->assertFalse($support->hasPermission('settings.manage'));
    }

    public function test_a_regular_customer_with_no_platform_role_has_no_platform_permissions(): void
    {
        $customer = User::factory()->create();

        $this->assertFalse($customer->hasPermission('users.manage'));
        $this->assertFalse($customer->isSuperAdmin());
    }

    public function test_user_cannot_view_another_users_profile(): void
    {
        $viewer = User::factory()->create();
        $other = User::factory()->create();

        $this->assertFalse($viewer->can('view', $other));
        $this->assertTrue($viewer->can('view', $viewer));
    }

    public function test_platform_role_middleware_blocks_unauthorized_roles(): void
    {
        \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'role:super_admin,support'])
            ->get('/_test/staff-only', fn () => 'ok');

        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/_test/staff-only')->assertForbidden();

        $support = User::factory()->create();
        $support->roles()->attach(Role::where('name', Role::SUPPORT)->first());

        $this->actingAs($support)->get('/_test/staff-only')->assertOk();
    }
}
