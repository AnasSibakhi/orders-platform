<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'user_id' => User::factory(),
            'role' => TeamMember::AGENT,
            'status' => TeamMember::STATUS_ACTIVE,
            'joined_at' => now(),
        ];
    }

    public function owner(): static
    {
        return $this->state(['role' => TeamMember::OWNER]);
    }

    public function manager(): static
    {
        return $this->state(['role' => TeamMember::MANAGER]);
    }
}
