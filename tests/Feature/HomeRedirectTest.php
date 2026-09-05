<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/home');

        $response->assertRedirect('/login');
    }

    public function test_user_with_no_business_is_sent_to_onboarding(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/home');

        $response->assertRedirect(route('onboarding.create'));
    }

    public function test_user_with_one_business_is_sent_straight_to_its_dashboard(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $business = Business::factory()->create();
        TeamMember::factory()->owner()->create(['business_id' => $business->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/home');

        $response->assertRedirect(route('business.dashboard', $business));
    }

    public function test_user_with_multiple_businesses_is_sent_to_the_switcher(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        foreach (range(1, 2) as $i) {
            $business = Business::factory()->create();
            TeamMember::factory()->owner()->create(['business_id' => $business->id, 'user_id' => $user->id]);
        }

        $response = $this->actingAs($user)->get('/home');

        $response->assertRedirect(route('business.switch'));
    }
}
