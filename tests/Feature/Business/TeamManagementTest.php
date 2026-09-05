<?php

namespace Tests\Feature\Business;

use App\Models\Business;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_an_existing_registered_user_to_the_team(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $business = Business::factory()->create();
        TeamMember::factory()->owner()->create(['business_id' => $business->id, 'user_id' => $owner->id]);

        $newMember = User::factory()->create(['email' => 'agent@example.com']);

        $response = $this->actingAs($owner)->post(route('team.store', $business), [
            'email' => 'agent@example.com',
            'role' => 'agent',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($business->teamMembers()->where('user_id', $newMember->id)->exists());
    }

    public function test_agent_cannot_add_team_members(): void
    {
        $agent = User::factory()->create(['email_verified_at' => now()]);
        $business = Business::factory()->create();
        TeamMember::factory()->create([
            'business_id' => $business->id,
            'user_id' => $agent->id,
            'role' => TeamMember::AGENT,
        ]);

        $response = $this->actingAs($agent)->post(route('team.store', $business), [
            'email' => 'someone@example.com',
            'role' => 'agent',
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_add_a_user_who_is_not_registered_yet(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $business = Business::factory()->create();
        TeamMember::factory()->owner()->create(['business_id' => $business->id, 'user_id' => $owner->id]);

        $response = $this->actingAs($owner)->post(route('team.store', $business), [
            'email' => 'nobody@example.com',
            'role' => 'agent',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_the_owner_cannot_be_removed(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $business = Business::factory()->create();
        $ownerMembership = TeamMember::factory()->owner()->create(['business_id' => $business->id, 'user_id' => $owner->id]);

        $manager = User::factory()->create();
        TeamMember::factory()->manager()->create(['business_id' => $business->id, 'user_id' => $manager->id]);

        $response = $this->actingAs($manager)->delete(route('team.destroy', [$business, $ownerMembership]));

        $response->assertSessionHasErrors('member');
        $this->assertDatabaseHas('team_members', ['id' => $ownerMembership->id]);
    }
}
