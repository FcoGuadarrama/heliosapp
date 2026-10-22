<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_users_with_clients_view_permission_can_view_clients_index()
    {
        $user = User::factory()->create();
        $user->syncRoles(['viewer']);

        $this->actingAs($user)
            ->get('/clients')
            ->assertStatus(200);
    }

    public function test_users_with_clients_create_can_create_a_client()
    {
        $user = User::factory()->create();
        $user->syncRoles(['sales']);

        $this->actingAs($user)
            ->post('/clients', [
                'type' => 'residential',
                'name' => 'Test Client',
                'contact_name' => 'John Doe',
                'email' => 'test@test.com',
                'phone' => '5555555555',
                'rfc' => 'ABC123ABC12',
                'street' => '123 Main St',
                'neighborhood' => 'Downtown',
                'municipality' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
            ])
            ->assertRedirect('/clients');

        $this->assertCount(1, Client::all());
        $this->assertEquals('Test Client', Client::first()->name);
    }

    public function test_creating_a_client_validates_required_fields()
    {
        $user = User::factory()->create();
        $user->syncRoles(['sales']);

        $this->actingAs($user)
            ->post('/clients', [
                'type' => 'invalid',
                'name' => '',
            ])
            ->assertSessionHasErrors(['type', 'name']);
    }

    public function test_creating_a_client_validates_email_format()
    {
        $user = User::factory()->create();
        $user->syncRoles(['sales']);

        $this->actingAs($user)
            ->post('/clients', [
                'type' => 'residential',
                'name' => 'Test',
                'email' => 'invalid-email',
            ])
            ->assertSessionHasErrors(['email']);
    }

    public function test_creating_a_client_validates_rfc_format()
    {
        $user = User::factory()->create();
        $user->syncRoles(['sales']);

        $this->actingAs($user)
            ->post('/clients', [
                'type' => 'residential',
                'name' => 'Test',
                'rfc' => 'invalid@rfc',
            ])
            ->assertSessionHasErrors(['rfc']);
    }

    public function test_creating_a_client_validates_postal_code_format()
    {
        $user = User::factory()->create();
        $user->syncRoles(['sales']);

        $this->actingAs($user)
            ->post('/clients', [
                'type' => 'residential',
                'name' => 'Test',
                'postal_code' => '123',
            ])
            ->assertSessionHasErrors(['postal_code']);
    }
}
