<?php

namespace Tests\Feature\Tenancy;

use App\Models\Business;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\Order;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * This is the single most important test file in the project: a bug here
 * means one tenant's data leaking to another, which is the worst possible
 * failure mode for a multi-tenant SaaS.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;

    protected User $userB;

    protected Business $businessA;

    protected Business $businessB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->create(['email_verified_at' => now()]);
        $this->userB = User::factory()->create(['email_verified_at' => now()]);

        $this->businessA = Business::factory()->create(['name' => 'Business A']);
        $this->businessB = Business::factory()->create(['name' => 'Business B']);

        TeamMember::factory()->owner()->create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->userA->id,
        ]);

        TeamMember::factory()->owner()->create([
            'business_id' => $this->businessB->id,
            'user_id' => $this->userB->id,
        ]);
    }

    public function test_a_non_member_gets_404_on_another_businesss_dashboard(): void
    {
        $response = $this->actingAs($this->userA)->get(route('business.dashboard', $this->businessB));

        $response->assertNotFound();
    }

    public function test_orders_index_never_returns_another_businesss_orders(): void
    {
        Order::factory()->count(3)->create(['business_id' => $this->businessA->id, 'customer_id' => Customer::factory()->create(['business_id' => $this->businessA->id])]);
        $otherOrder = Order::factory()->create(['business_id' => $this->businessB->id, 'customer_id' => Customer::factory()->create(['business_id' => $this->businessB->id])]);

        $response = $this->actingAs($this->userA)->get(route('orders.index', $this->businessA));

        $response->assertOk();
        $response->assertDontSee('#'.$otherOrder->id);
    }

    public function test_a_member_of_business_a_cannot_fetch_an_order_belonging_to_business_b_by_guessing_its_id(): void
    {
        $customerB = Customer::factory()->create(['business_id' => $this->businessB->id]);
        $orderB = Order::factory()->create(['business_id' => $this->businessB->id, 'customer_id' => $customerB->id]);

        // userA is a legitimate member of businessA, tries to view businessB's
        // order by hitting businessA's URL prefix with businessB's order id.
        $response = $this->actingAs($this->userA)->get(route('orders.show', [$this->businessA, $orderB->id]));

        $response->assertNotFound();
    }

    public function test_a_member_cannot_update_status_of_another_businesss_order(): void
    {
        $customerB = Customer::factory()->create(['business_id' => $this->businessB->id]);
        $orderB = Order::factory()->create([
            'business_id' => $this->businessB->id,
            'customer_id' => $customerB->id,
            'status' => Order::STATUS_NEW,
        ]);

        $response = $this->actingAs($this->userA)
            ->patch(route('orders.status', [$this->businessA, $orderB->id]), ['status' => Order::STATUS_COMPLETED]);

        $response->assertNotFound();
        $this->assertSame(Order::STATUS_NEW, $orderB->fresh()->status);
    }

    public function test_customers_index_never_returns_another_businesss_customers(): void
    {
        $customerB = Customer::factory()->create(['business_id' => $this->businessB->id, 'name' => 'Customer From B']);

        $response = $this->actingAs($this->userA)->get(route('customers.index', $this->businessA));

        $response->assertOk();
        $response->assertDontSee('Customer From B');
    }

    public function test_channels_index_never_returns_another_businesss_channels(): void
    {
        Channel::factory()->create(['business_id' => $this->businessB->id, 'name' => 'B Secret Channel']);

        $response = $this->actingAs($this->userA)->get(route('channels.index', $this->businessA));

        $response->assertOk();
        $response->assertDontSee('B Secret Channel');
    }

    public function test_eloquent_global_scope_blocks_cross_tenant_reads_even_at_the_model_level(): void
    {
        $customerB = Customer::factory()->create(['business_id' => $this->businessB->id]);
        Order::factory()->count(2)->create(['business_id' => $this->businessB->id, 'customer_id' => $customerB->id]);

        // Simulate being "inside" business A's tenant context and query
        // Order directly — must not see business B's rows even without
        // going through a controller.
        app(\App\Support\Tenancy\CurrentBusiness::class)->set($this->businessA);

        $this->assertSame(0, Order::count());

        app(\App\Support\Tenancy\CurrentBusiness::class)->set($this->businessB);

        $this->assertSame(2, Order::count());
    }
}
