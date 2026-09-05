<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'customer_id' => Customer::factory(),
            'status' => Order::STATUS_NEW,
            'total' => fake()->randomFloat(2, 20, 500),
            'currency' => 'SAR',
        ];
    }
}
