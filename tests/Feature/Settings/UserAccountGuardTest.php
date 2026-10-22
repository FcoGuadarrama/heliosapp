<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccountGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_user_cannot_deactivate_themselves(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $secondOwner = User::factory()->create();
        $secondOwner->assignRole('owner');

        $response = $this
            ->actingAs($owner)
            ->patch(route('users.toggle-active', $owner));

        $response->assertSessionHasErrors('is_active');
        $this->assertTrue($owner->refresh()->is_active);
    }

    public function test_a_user_cannot_change_their_own_role(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $secondOwner = User::factory()->create();
        $secondOwner->assignRole('owner');

        $response = $this
            ->actingAs($owner)
            ->patch(route('users.update', $owner), [
                'name' => $owner->name,
                'email' => $owner->email,
                'role' => 'admin',
            ]);

        $response->assertSessionHasErrors('role');
        $this->assertTrue($owner->refresh()->hasRole('owner'));
    }

    public function test_the_last_active_owner_cannot_be_deactivated(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $lastOwner = User::factory()->create();
        $lastOwner->assignRole('owner');

        $response = $this
            ->actingAs($admin)
            ->patch(route('users.toggle-active', $lastOwner));

        $response->assertSessionHasErrors('is_active');
        $this->assertTrue($lastOwner->refresh()->is_active);
    }

    public function test_the_last_active_owner_cannot_be_demoted(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $lastOwner = User::factory()->create();
        $lastOwner->assignRole('owner');

        $response = $this
            ->actingAs($admin)
            ->patch(route('users.update', $lastOwner), [
                'name' => $lastOwner->name,
                'email' => $lastOwner->email,
                'role' => 'admin',
            ]);

        $response->assertSessionHasErrors('role');
        $this->assertTrue($lastOwner->refresh()->hasRole('owner'));
    }

    public function test_an_owner_can_be_deactivated_when_another_active_owner_remains(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $otherOwner = User::factory()->create();
        $otherOwner->assignRole('owner');

        $response = $this
            ->actingAs($owner)
            ->patch(route('users.toggle-active', $otherOwner));

        $response->assertSessionHasNoErrors();
        $this->assertFalse($otherOwner->refresh()->is_active);
    }
}
