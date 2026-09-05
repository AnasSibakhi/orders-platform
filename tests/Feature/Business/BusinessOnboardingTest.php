<?php

namespace Tests\Feature\Business;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_business_makes_the_creator_its_owner(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'name' => 'متجر تجريبي',
        ]);

        $business = $user->businesses()->first();

        $this->assertNotNull($business);
        $this->assertSame('متجر تجريبي', $business->name);
        $this->assertSame(TeamMember::OWNER, $business->pivot->role);
        $response->assertRedirect(route('business.dashboard', $business));
    }

    public function test_business_slug_is_unique_even_for_duplicate_names(): void
    {
        $userOne = User::factory()->create(['email_verified_at' => now()]);
        $userTwo = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($userOne)->post(route('onboarding.store'), ['name' => 'My Shop']);
        $this->actingAs($userTwo)->post(route('onboarding.store'), ['name' => 'My Shop']);

        $slugs = \App\Models\Business::pluck('slug');

        $this->assertSame($slugs->count(), $slugs->unique()->count());
    }
}
