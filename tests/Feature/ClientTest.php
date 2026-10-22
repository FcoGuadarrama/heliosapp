<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Policies\ClientPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->withoutVite();
    }

    public function test_users_with_clients_view_permission_can_view_clients_index()
    {
        $user = User::factory()->create();
        $user->syncRoles(['viewer']);

        $this->actingAs($user)
            ->get('/clients')
            ->assertStatus(200);
    }

    public function test_client_details_do_not_expose_projects_without_project_permission(): void
    {
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $user = User::factory()->create();
        $user->givePermissionTo('clients.view');

        $this->actingAs($user)->get('/clients/'.$client->id)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('client.id', $client->id)->has('projects', 0));

        $user->givePermissionTo('projects.view');
        $this->get('/clients/'.$client->id)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('projects', 1)->where('projects.0.id', $project->id));
    }

    public function test_contact_can_be_edited_without_classifying_a_prospect(): void
    {
        $client = Client::factory()->create(['type' => null]);
        $user = User::factory()->create();
        $user->assignRole('sales');
        $this->actingAs($user);

        foreach ([[], ['type' => null], ['type' => '']] as $classification) {
            $this->patch('/clients/'.$client->id, [...$classification, 'name' => 'Contacto actualizado', 'phone' => '5551234567'])
                ->assertRedirect('/clients');
            $this->assertNull($client->fresh()->type);
            $this->assertSame('5551234567', $client->fresh()->phone);
        }
    }

    public function test_contact_edit_preserves_existing_classification_and_rejects_unknown_types(): void
    {
        $client = Client::factory()->create(['type' => 'commercial']);
        $user = User::factory()->create();
        $user->assignRole('sales');
        $this->actingAs($user)->patch('/clients/'.$client->id, ['name' => 'Contacto actualizado'])->assertRedirect('/clients');
        $this->assertSame('commercial', $client->fresh()->type->value);

        $this->patch('/clients/'.$client->id, ['name' => 'No guardar', 'type' => 'unknown'])->assertSessionHasErrors('type');
        $this->assertSame('commercial', $client->fresh()->type->value);
        $this->assertSame('Contacto actualizado', $client->fresh()->name);
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

    public static function roles(): array
    {
        return [['owner', true, true, true], ['admin', true, true, true], ['sales', true, true, true], ['engineer', false, false, false], ['viewer', false, false, false]];
    }

    #[DataProvider('roles')]
    public function test_policies_and_crud_by_role(string $role, bool $create, bool $update, bool $delete): void
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $resource = Client::factory()->create();
        $this->assertInstanceOf(ClientPolicy::class, Gate::getPolicyFor(Client::class));
        foreach (['viewAny' => true, 'view' => true, 'create' => $create, 'update' => $update, 'delete' => $delete] as $ability => $allowed) {
            $this->assertSame($allowed, Gate::forUser($user)->allows($ability, in_array($ability, ['create', 'viewAny']) ? Client::class : $resource));
        }
        // Policies remain active when the route permission middleware is absent.
        $this->withoutMiddleware(PermissionMiddleware::class);
        $this->actingAs($user)->get('/clients')->assertOk();
        $this->get('/clients/'.$resource->id)->assertOk();
        $this->get('/clients/create')->assertStatus($create ? 200 : 403);
        $this->get('/clients/'.$resource->id.'/edit')->assertStatus($update ? 200 : 403);
        $this->post('/clients', ['type' => 'residential', 'name' => 'Nuevo'])->assertStatus($create ? 302 : 403);
        $this->patch('/clients/'.$resource->id, ['type' => 'commercial', 'name' => 'Editado'])->assertStatus($update ? 302 : 403);
        $this->assertSame($update ? 'Editado' : $resource->name, $resource->fresh()->name);
        $this->delete('/clients/'.$resource->id)->assertStatus($delete ? 302 : 403);
        if ($delete) {
            $this->assertSoftDeleted($resource);
            $this->get('/clients/'.$resource->id)->assertNotFound();
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
        $resource = Client::factory()->create();
        $this->mock(ClientPolicy::class, function ($mock) use ($resource, $ability, $action): void {
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
            'index' => $this->get('/clients'),
            'create' => $this->get('/clients/create'),
            'show' => $this->get('/clients/'.$resource->id),
            'edit' => $this->get('/clients/'.$resource->id.'/edit'),
            'store' => $this->post('/clients', ['type' => 'residential', 'name' => 'Nuevo']),
            'update' => $this->patch('/clients/'.$resource->id, ['type' => 'residential', 'name' => 'Nuevo']),
            'destroy' => $this->delete('/clients/'.$resource->id),
        };
        $response->assertForbidden();
        $this->assertNotSoftDeleted($resource);
    }

    public function test_search_pagination_and_project_history_preserve_resource_dates(): void
    {
        $user = User::factory()->create();
        $user->assignRole('sales');
        $client = Client::factory()->create(['name' => 'Solar buscable', 'created_at' => '2026-01-01 10:00:00']);
        Client::factory()->count(16)->create(['name' => 'Otro']);
        $project = Project::factory()->create(['client_id' => $client->id, 'created_at' => '2026-10-01 12:30:00']);
        $this->actingAs($user)->get('/clients?search=Solar%20buscable')->assertInertia(fn ($page) => $page
            ->has('clients.data', 1)->where('clients.data.0.id', $client->id));
        $this->get('/clients?page=2')->assertInertia(fn ($page) => $page->has('clients.data', 2));
        $this->get('/clients/'.$client->id)->assertInertia(fn ($page) => $page
            ->has('projects', 1)->where('projects.0.id', $project->id)
            ->where('projects.0.created_at', '2026-10-01 12:30'));
    }

    public function test_pagination_links_preserve_the_prospect_search(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');
        Client::factory()->count(16)->create(['name' => 'Solar buscable']);
        Client::factory()->count(16)->create(['name' => 'Otro prospecto']);

        $response = $this->actingAs($user)->get('/clients?search=Solar%20buscable');
        $response->assertInertia(fn ($page) => $page
            ->where('search', 'Solar buscable')
            ->where('clients.total', 16)
            ->has('clients.data', 15));

        $nextUrl = $response->viewData('page')['props']['clients']['next_page_url'];
        parse_str(parse_url($nextUrl, PHP_URL_QUERY), $query);
        $this->assertSame('Solar buscable', $query['search']);
        $this->assertSame('2', $query['page']);

        $this->get($nextUrl)->assertInertia(fn ($page) => $page
            ->where('search', 'Solar buscable')
            ->where('clients.total', 16)
            ->has('clients.data', 1)
            ->where('clients.data.0.name', 'Solar buscable'));
    }
}
