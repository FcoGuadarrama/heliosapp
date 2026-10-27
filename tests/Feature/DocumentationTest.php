<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_documentation_schema_and_assets_require_authentication(): void
    {
        foreach (['/documentation', '/documentation/specification', '/documentation/specification/asset/swagger-ui.css'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        $this->actingAs(User::factory()->unverified()->create());
        $this->get('/documentation')->assertRedirect(route('verification.notice'));
        $this->getJson('/documentation/specification')->assertForbidden();
    }

    public function test_generated_documentation_is_accessible_and_describes_registered_routes(): void
    {
        $directory = sys_get_temp_dir().'/helios-openapi-'.bin2hex(random_bytes(8));
        config(['l5-swagger.defaults.paths.docs' => $directory]);

        try {
            $this->artisan('l5-swagger:generate')->assertSuccessful();
            $this->actingAs(User::factory()->create());
            $this->get('/documentation')->assertOk()
                ->assertSee('Volver a Helios')->assertSee('supportedSubmitMethods: []', false);
            $this->get('/documentation', ['X-Inertia' => 'true'])->assertStatus(409)
                ->assertHeader('X-Inertia-Location', url('/documentation'));
            $this->get('/documentation/specification/asset/swagger-ui-bundle.js')->assertOk();
            $response = $this->getJson('/documentation/specification')->assertOk()
                ->assertJsonPath('openapi', '3.0.0')
                ->assertJsonPath('servers.0.url', '/')
                ->assertJsonPath('paths./embed/cfe.post.security', [])
                ->assertJsonPath('paths./intakes/{intake}/analysis.get.security.0.session', []);

            $specification = $response->json();
            $registered = [];
            foreach (Route::getRoutes() as $route) {
                foreach ($route->methods() as $method) {
                    $registered[] = strtolower($method).' /'.$route->uri();
                }
            }
            foreach ($specification['paths'] as $path => $operations) {
                foreach (array_keys($operations) as $method) {
                    $this->assertContains($method.' '.$path, $registered);
                }
            }
            $this->assertSame(
                ['revision', 'reason', 'confirmed', 'tariff_code', 'consumption_kwh', 'period_start', 'period_end', 'state_code', 'history', 'concepts'],
                $specification['components']['schemas']['ReceiptReviewInput']['required'],
            );
            $this->assertArrayNotHasKey('/api/v1/intakes', $specification['paths']);
            $this->assertFileExists($directory.'/helios.yaml');
        } finally {
            File::deleteDirectory($directory);
        }
    }
}
