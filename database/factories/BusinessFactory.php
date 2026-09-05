<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Business::generateUniqueSlug($name),
            'timezone' => 'Asia/Riyadh',
            'status' => Business::STATUS_ACTIVE,
        ];
    }
}
