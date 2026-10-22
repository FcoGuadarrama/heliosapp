<?php

namespace Tests\Feature\Settings;

use App\Models\CompanySetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function ownerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('owner');

        return $user;
    }

    public function test_company_settings_page_is_displayed()
    {
        $user = $this->ownerUser();

        $response = $this
            ->actingAs($user)
            ->get(route('company.edit'));

        $response->assertOk();
    }

    public function test_unauthenticated_user_is_redirected()
    {
        $response = $this->get(route('company.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_company_settings_can_be_updated()
    {
        $user = $this->ownerUser();

        $response = $this
            ->actingAs($user)
            ->patch(route('company.update'), [
                'company_name' => 'Mi Empresa Solar',
                'legal_name' => 'Mi Empresa Solar S.A. de C.V.',
                'rfc' => 'MES123456XYZ',
                'phone' => '+52 55 1234 5678',
                'email' => 'info@empresa.mx',
                'website' => 'https://empresa.mx',
                'timezone' => 'America/Mexico_City',
                'default_currency' => 'MXN',
                'proposal_validity_days' => 30,
                'responsible_name' => 'Juan Pérez',
                'responsible_title' => 'Director General',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('company.edit'));

        $settings = CompanySetting::current();
        $this->assertSame('Mi Empresa Solar', $settings->company_name);
        $this->assertSame('Mi Empresa Solar S.A. de C.V.', $settings->legal_name);
        $this->assertSame('MES123456XYZ', $settings->rfc);
        $this->assertSame('+52 55 1234 5678', $settings->phone);
        $this->assertSame('info@empresa.mx', $settings->email);
        $this->assertSame('https://empresa.mx', $settings->website);
        $this->assertSame('America/Mexico_City', $settings->timezone);
        $this->assertSame('MXN', $settings->default_currency);
        $this->assertSame(30, $settings->proposal_validity_days);
        $this->assertSame('Juan Pérez', $settings->responsible_name);
        $this->assertSame('Director General', $settings->responsible_title);
    }

    public function test_validation_rejects_invalid_rfc()
    {
        $user = $this->ownerUser();

        $response = $this
            ->actingAs($user)
            ->patch(route('company.update'), [
                'rfc' => 'TOO_LONG_RFC_STRING_12345',
                'timezone' => 'America/Mexico_City',
                'default_currency' => 'MXN',
                'proposal_validity_days' => 30,
            ]);

        $response->assertSessionHasErrors('rfc');
    }

    public function test_validation_rejects_invalid_color()
    {
        $user = $this->ownerUser();

        $response = $this
            ->actingAs($user)
            ->patch(route('company.update'), [
                'primary_color' => 'invalid_color',
                'timezone' => 'America/Mexico_City',
                'default_currency' => 'MXN',
                'proposal_validity_days' => 30,
            ]);

        $response->assertSessionHasErrors('primary_color');
    }

    public function test_company_settings_only_has_one_row()
    {
        $user = $this->ownerUser();

        // First update
        $this
            ->actingAs($user)
            ->patch(route('company.update'), [
                'company_name' => 'Empresa 1',
                'timezone' => 'America/Mexico_City',
                'default_currency' => 'MXN',
                'proposal_validity_days' => 30,
            ]);

        // Second update
        $this
            ->actingAs($user)
            ->patch(route('company.update'), [
                'company_name' => 'Empresa 2',
                'timezone' => 'America/Mexico_City',
                'default_currency' => 'MXN',
                'proposal_validity_days' => 30,
            ]);

        // Only one row should exist
        $this->assertSame(1, CompanySetting::count());
        $this->assertSame('Empresa 2', CompanySetting::current()->company_name);
    }

    public function test_validation_rejects_invalid_proposal_validity_days()
    {
        $user = $this->ownerUser();

        $response = $this
            ->actingAs($user)
            ->patch(route('company.update'), [
                'timezone' => 'America/Mexico_City',
                'default_currency' => 'MXN',
                'proposal_validity_days' => 366, // Max is 365
            ]);

        $response->assertSessionHasErrors('proposal_validity_days');
    }

    public function test_validation_accepts_valid_color()
    {
        $user = $this->ownerUser();

        $response = $this
            ->actingAs($user)
            ->patch(route('company.update'), [
                'primary_color' => '#FF5733',
                'secondary_color' => '#33FF57',
                'timezone' => 'America/Mexico_City',
                'default_currency' => 'MXN',
                'proposal_validity_days' => 30,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('company.edit'));

        $settings = CompanySetting::current();
        $this->assertSame('#FF5733', $settings->primary_color);
        $this->assertSame('#33FF57', $settings->secondary_color);
    }
}
