<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Policies\ProjectPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->withoutVite();
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

    public static function roles(): array
    {
        return [['owner', true, true, true], ['admin', true, true, true], ['sales', true, true, true], ['engineer', false, true, false], ['viewer', false, false, false]];
    }

    #[DataProvider('roles')]
    public function test_policies_and_crud_by_role(string $role, bool $create, bool $update, bool $delete): void
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $resource = Project::factory()->create();
        $this->assertInstanceOf(ProjectPolicy::class, Gate::getPolicyFor(Project::class));
        foreach (['viewAny' => true, 'view' => true, 'create' => $create, 'update' => $update, 'delete' => $delete] as $ability => $allowed) {
            $this->assertSame($allowed, Gate::forUser($user)->allows($ability, in_array($ability, ['create', 'viewAny']) ? Project::class : $resource));
        }
        // Policies remain active when the route permission middleware is absent.
        $this->withoutMiddleware(PermissionMiddleware::class);
        $this->actingAs($user)->get('/projects')->assertOk();
        $this->get('/projects/'.$resource->id)->assertOk();
        $this->get('/projects/create')->assertStatus($create ? 200 : 403);
        $this->get('/projects/'.$resource->id.'/edit')->assertStatus($update ? 200 : 403);
        $this->post('/projects', ['client_id' => $resource->client_id, 'name' => 'Nuevo', 'status' => 'draft', 'installation_type' => 'roof'])->assertStatus($create ? 302 : 403);
        $this->patch('/projects/'.$resource->id, ['client_id' => $resource->client_id, 'name' => 'Editado', 'status' => 'quoted', 'installation_type' => 'ground'])->assertStatus($update ? 302 : 403);
        $this->assertSame($update ? 'Editado' : $resource->name, $resource->fresh()->name);
        $this->delete('/projects/'.$resource->id)->assertStatus($delete ? 302 : 403);
        if ($delete) {
            $this->assertSoftDeleted($resource);
            $this->get('/projects/'.$resource->id)->assertNotFound();
        } else {
            $this->assertNotSoftDeleted($resource);
        }
    }

    public static function actions(): array
    {
        return [['index', 'viewAny'], ['create', 'create'], ['store', 'create'], ['show', 'view'], ['edit', 'update'], ['update', 'update'], ['destroy', 'delete']];
    }

    #[DataProvider('actions')]
    public function test_controller_enforces_resource_policy_even_with_route_permission(string $action, string $ability): void
    {
        $user = User::factory()->create();
        $user->assignRole('owner');
        $resource = Project::factory()->create();
        $this->mock(ProjectPolicy::class, function ($mock) use ($resource, $ability, $action): void {
            $expectation = $mock->shouldReceive($ability)->once();
            if (in_array($action, ['index', 'create', 'store'])) {
                $expectation->withArgs(fn ($user) => $user->hasRole('owner'));
            } else {
                $expectation->withArgs(fn ($user, $model) => $model->id === $resource->id);
            }
            $expectation->andReturn(false);
        });
        $this->actingAs($user);
        $response = match ($action) {
            'index' => $this->get('/projects'),
            'create' => $this->get('/projects/create'),
            'show' => $this->get('/projects/'.$resource->id),
            'edit' => $this->get('/projects/'.$resource->id.'/edit'),
            'store' => $this->post('/projects', ['client_id' => $resource->client_id, 'name' => 'Nuevo', 'status' => 'draft', 'installation_type' => 'roof']),
            'update' => $this->patch('/projects/'.$resource->id, ['client_id' => $resource->client_id, 'name' => 'Nuevo', 'status' => 'draft', 'installation_type' => 'roof']),
            'destroy' => $this->delete('/projects/'.$resource->id),
        };
        $response->assertForbidden();
        $this->assertNotSoftDeleted($resource);
    }

    public function test_generated_codes_include_deleted_history_and_do_not_truncate_large_sequences(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sales');
        $client = Client::factory()->create();
        $previous = Project::factory()->create(['client_id' => $client->id, 'code' => 'PRY-'.now()->format('Y').'-9999']);
        $previous->delete();
        $data = ['client_id' => $client->id, 'name' => 'Nuevo', 'status' => 'draft', 'installation_type' => 'roof', 'code' => 'attacker-code', 'created_by' => 9999];
        $this->actingAs($user)->post('/projects', $data)->assertRedirect('/projects');
        $project = Project::sole();
        $this->assertSame('PRY-'.now()->format('Y').'-10000', $project->code);
        $this->assertSame($user->id, $project->created_by);
        $this->post('/projects', $data)->assertRedirect('/projects');
        $this->assertDatabaseHas('projects', ['code' => 'PRY-'.now()->format('Y').'-10001']);
    }

    public function test_json_coordinates_and_deleted_client_validation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sales');
        $client = Client::factory()->create();
        $data = ['client_id' => $client->id, 'name' => 'Sitio', 'status' => 'draft', 'installation_type' => 'roof'];
        $this->actingAs($user)->post('/projects', [...$data, 'latitude' => 91, 'longitude' => -181, 'objectives' => 'invalid', 'property_data' => 'invalid'])->assertSessionHasErrors(['latitude', 'longitude', 'objectives', 'property_data']);
        $this->post('/projects', [...$data, 'objectives' => ['preferences' => 'Sin baterías'], 'property_data' => ['shading' => 'Pendiente']])->assertRedirect();
        $project = Project::sole();
        $this->assertSame(['preferences' => 'Sin baterías'], $project->objectives);
        $this->assertTrue($project->isMissingCoordinates());
        $this->patch('/projects/'.$project->id, [...$data, 'latitude' => 0, 'longitude' => 0])->assertRedirect();
        $this->assertFalse($project->fresh()->isMissingCoordinates());
        $client->delete();
        $this->post('/projects', $data)->assertSessionHasErrors('client_id');
        // History still renders after a client was soft-deleted.
        $this->get('/projects')->assertOk();
    }
}
