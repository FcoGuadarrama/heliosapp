<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_seeder_creates_the_five_roles(): void
    {
        $this->assertSame(
            ['admin', 'engineer', 'owner', 'sales', 'viewer'],
            Role::query()->orderBy('name')->pluck('name')->all(),
        );
    }

    public function test_seeder_runs_twice_without_duplicating_or_losing_assignments(): void
    {
        $permissionsBefore = Permission::count();
        $rolesBefore = Role::count();

        $user = User::factory()->create();
        $user->assignRole('sales');

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame($permissionsBefore, Permission::count());
        $this->assertSame($rolesBefore, Role::count());

        $user->refresh();
        $this->assertTrue($user->hasRole('sales'));
    }

    public function test_owner_has_every_permission(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this->assertTrue($owner->can('clients.create'));
        $this->assertTrue($owner->can('users.delete'));
        $this->assertTrue($owner->can('settings.update'));
    }

    public function test_admin_cannot_delete_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('users.update'));
        $this->assertFalse($admin->can('users.delete'));
    }

    public function test_viewer_is_denied_write_permissions_and_sales_is_allowed(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $sales = User::factory()->create();
        $sales->assignRole('sales');

        $this->assertTrue(Gate::forUser($viewer)->denies('clients.create'));
        $this->assertTrue(Gate::forUser($sales)->allows('clients.create'));

        $this->assertTrue(Gate::forUser($viewer)->denies('users.create'));
    }

    public function test_viewer_receives_403_when_creating_a_user(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $response = $this
            ->actingAs($viewer)
            ->post(route('users.store'), [
                'name' => 'Nuevo usuario',
                'email' => 'nuevo@test.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'viewer',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@test.com']);
    }

    public function test_owner_can_create_a_user(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $response = $this
            ->actingAs($owner)
            ->post(route('users.store'), [
                'name' => 'Nuevo usuario',
                'email' => 'nuevo@test.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'engineer',
            ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo@test.com']);

        $created = User::where('email', 'nuevo@test.com')->first();
        $this->assertTrue($created->hasRole('engineer'));
    }

    public function test_users_index_renders_the_expected_inertia_component(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this
            ->actingAs($owner)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/Users/Index')
                ->has('users')
                ->has('roles'));
    }
}
