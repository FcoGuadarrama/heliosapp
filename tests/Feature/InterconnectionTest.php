<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->withoutVite();
});

it('requires authentication on both calculator routes', function () {
    $this->get('/engineering/interconnection')->assertRedirect('/login');
    $this->post('/engineering/interconnection', [])->assertRedirect('/login');
});

it('denies access without permission on both routes', function () {
    $user = User::factory()->create();
    $user->assignRole('viewer');
    $this->actingAs($user)->get('/engineering/interconnection')->assertForbidden();
    $this->post('/engineering/interconnection', [])->assertForbidden();
});

it('allows receipt operators to open the manual sizing screen', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user)->get('/engineering/interconnection')->assertInertia(fn (Assert $page) => $page
        ->component('Engineering/Interconnection')
        ->where('result', null));
})->with(['owner', 'admin', 'engineer', 'sales']);

it('calculates manual inputs without creating a commercial proposal', function () {
    $user = User::factory()->create();
    $user->assignRole('engineer');
    $this->actingAs($user)->post('/engineering/interconnection', [
        'current_kwh' => '600', 'period_days' => '60', 'hsp' => '5', 'module_w' => '550',
        'history_kwh' => [], 'modules_per_micro' => '2',
    ])->assertInertia(fn (Assert $page) => $page
        ->component('Engineering/Interconnection')
        ->where('result.moduleCount', 5)
        ->where('result.minimumInverterW', 2165)
        ->where('result.microinverterCount', 3)
        ->where('result.consumptionSource', 'current_period'));

    $this->assertDatabaseCount('projects', 0);
    $this->assertDatabaseCount('clients', 0);
});

it('accepts annual history even if current consumption is zero', function () {
    $user = User::factory()->create();
    $user->assignRole('engineer');
    $this->actingAs($user)->post('/engineering/interconnection', [
        'current_kwh' => 0, 'period_days' => 60, 'hsp' => 5, 'module_w' => 550,
        'history_kwh' => [0, 600, 600, 600, 600, 1200],
    ])->assertInertia(fn (Assert $page) => $page
        ->where('result.dailyConsumptionKwh', 10)
        ->where('result.consumptionSource', 'history')
        ->where('result.microinverterCount', null));
});

it('returns actionable validation errors instead of computing with invalid data', function (array $changes, string $field) {
    $user = User::factory()->create();
    $user->assignRole('engineer');
    $data = array_replace([
        'current_kwh' => 600, 'period_days' => 60, 'hsp' => 5, 'module_w' => 550,
        'history_kwh' => [],
    ], $changes);

    $this->actingAs($user)->from('/engineering/interconnection')
        ->post('/engineering/interconnection', $data)
        ->assertRedirect('/engineering/interconnection')
        ->assertSessionHasErrors($field);
})->with([
    'zero consumption' => [['current_kwh' => 0], 'current_kwh'],
    'missing HSP' => [['hsp' => null], 'hsp'],
    'zero days' => [['period_days' => 0], 'period_days'],
    'fractional days' => [['period_days' => 60.5], 'period_days'],
    'negative module power' => [['module_w' => -1], 'module_w'],
    'invalid historic entry' => [['history_kwh' => [600, 'abc']], 'history_kwh.1'],
    'empty historic entry' => [['history_kwh' => ['']], 'history_kwh.0'],
    'oversized history' => [['history_kwh' => array_fill(0, 25, 600)], 'history_kwh'],
    'non-list history' => [['history_kwh' => ['period' => 600]], 'history_kwh'],
    'fractional micro capacity' => [['modules_per_micro' => 2.5], 'modules_per_micro'],
]);
