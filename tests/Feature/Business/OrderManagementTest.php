<?php

namespace Tests\Feature\Business;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_order_status_records_history(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $business = Business::factory()->create();
        TeamMember::factory()->owner()->create(['business_id' => $business->id, 'user_id' => $user->id]);

        $customer = Customer::factory()->create(['business_id' => $business->id]);
        $order = Order::factory()->create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'status' => Order::STATUS_NEW,
        ]);

        $response = $this->actingAs($user)
            ->patch(route('orders.status', [$business, $order]), ['status' => Order::STATUS_PROCESSING]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(Order::STATUS_PROCESSING, $order->fresh()->status);

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'from_status' => Order::STATUS_NEW,
            'to_status' => Order::STATUS_PROCESSING,
            'changed_by_user_id' => $user->id,
        ]);
    }

    public function test_rejects_an_invalid_status(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $business = Business::factory()->create();
        TeamMember::factory()->owner()->create(['business_id' => $business->id, 'user_id' => $user->id]);

        $customer = Customer::factory()->create(['business_id' => $business->id]);
        $order = Order::factory()->create(['business_id' => $business->id, 'customer_id' => $customer->id]);

        $response = $this->actingAs($user)
            ->patch(route('orders.status', [$business, $order]), ['status' => 'not_a_real_status']);

        $response->assertSessionHasErrors('status');
    }
}
