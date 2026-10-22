<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_users_with_projects_view_permission_can_view_projects_index()
    {
        $user = User::factory()->create();
        $user->syncRoles(['viewer']);

        $this->actingAs($user)
            ->get('/projects')
            ->assertStatus(200);
    }

    public function test_creating_a_project_validates_required_fields()
    {
        $user = User::factory()->create();
        $user->syncRoles(['sales']);

        $this->actingAs($user)
            ->post('/projects', [
                'name' => '',
                'status' => 'invalid',
            ])
            ->assertSessionHasErrors(['client_id', 'name', 'status', 'installation_type']);
    }
}
