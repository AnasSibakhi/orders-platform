<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'type' => Channel::TYPE_WHATSAPP,
            'name' => 'WhatsApp — '.fake()->word(),
            'status' => Channel::STATUS_DISCONNECTED,
        ];
    }
}
