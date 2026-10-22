<?php

namespace Tests\Feature;

use App\Application\StoreReceiptIntake;
use App\Domain\Intake\IntakeSettings;
use App\Domain\Intake\PublicFormToken;
use App\Models\Client;
use App\Models\ReceiptIntake;
use App\Models\User;
use App\Policies\ClientPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IntakeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('receipts');
        config(['intake.enabled' => true, 'intake.privacy_version' => 'test-v1', 'intake.privacy_text' => 'Aviso ficticio solo para pruebas.', 'intake.frame_ancestors' => ['https://instaladora.example']]);
        $this->withoutVite();
    }

    private function file(string $name = 'recibo.pdf'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'helios-upload-');
        file_put_contents($path, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");

        return new UploadedFile($path, $name, 'application/pdf', null, true);
    }

    private function data(): array
    {
        return ['name' => 'Prospecto Prueba', 'email' => 'prospecto@example.test', 'privacy_consent' => '1', 'privacy_version' => 'test-v1', 'receipt' => $this->file()];
    }

    private function publicData(): array
    {
        $token = app(PublicFormToken::class)->issue();
        $this->travel(3)->seconds();

        return [...$this->data(), 'form_token' => $token];
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_embed_is_stateless_and_has_strict_csp_including_errors(): void
    {
        $response = $this->get('/embed/cfe')->assertOk()->assertViewHas('formToken');
        $this->assertStringContainsString('frame-ancestors https://instaladora.example', $response->headers->get('Content-Security-Policy'));
        $this->assertFalse($response->headers->has('Set-Cookie'));
        $this->assertFalse($response->headers->has('Access-Control-Allow-Origin'));
        $response = $this->post('/embed/cfe', [])->assertStatus(422);
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        config(['intake.enabled' => false]);
        $this->get('/embed/cfe')->assertStatus(503)->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_public_upload_persists_private_file_consent_and_prospect_without_project(): void
    {
        $this->post('/embed/cfe', $this->publicData())->assertStatus(303)->assertRedirect('/embed/cfe?submitted=1');
        $intake = ReceiptIntake::sole();
        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseCount('projects', 0);
        $this->assertSame('embed', $intake->source);
        $this->assertNull($intake->client->type);
        $this->assertSame('stored', $intake->status);
        $this->assertNull($intake->created_by);
        $this->assertSame('test-v1', $intake->privacy_version);
        $this->assertSame(config('intake.privacy_text'), $intake->privacy_text);
        $this->assertNotNull($intake->consented_at);
        $this->assertNull($intake->raw_payload);
        Storage::disk('receipts')->assertExists($intake->file_path);
        $this->assertArrayNotHasKey('file_path', $intake->toArray());
        $this->get('/storage/'.$intake->file_path)->assertForbidden();
        $this->get('/intakes/'.$intake->id.'/file')->assertRedirect('/login');
    }

    public function test_public_matching_contact_never_links_or_updates_existing_prospect(): void
    {
        $existing = Client::factory()->create(['name' => 'Privado', 'email' => 'prospecto@example.test']);
        $response = $this->post('/embed/cfe', $this->publicData())->assertStatus(303);
        $this->assertDatabaseCount('clients', 2);
        $this->assertNotSame($existing->id, ReceiptIntake::sole()->client_id);
        $this->assertSame('Privado', $existing->fresh()->name);
        $this->assertStringNotContainsString('Privado', $response->getContent());
        $data = $this->publicData();
        $this->post('/embed/cfe', [...$data, 'client_id' => $existing->id])->assertStatus(422);
        $this->assertDatabaseCount('receipt_intakes', 1);
    }

    public function test_token_is_required_delayed_expiring_and_single_use(): void
    {
        $this->post('/embed/cfe', [...$this->data(), 'form_token' => 'tampered'])->assertStatus(422);
        $token = app(PublicFormToken::class)->issue();
        $this->post('/embed/cfe', [...$this->data(), 'form_token' => $token])->assertStatus(422);
        $this->travel(3)->seconds();
        $this->post('/embed/cfe', [...$this->data(), 'form_token' => $token])->assertStatus(303);
        $this->post('/embed/cfe', [...$this->data(), 'form_token' => $token])->assertStatus(422);
        $this->assertDatabaseCount('receipt_intakes', 1);
        $this->travel(31)->minutes();
        $this->post('/embed/cfe', [...$this->data(), 'form_token' => $token])->assertStatus(422);
    }

    public function test_honeypot_and_rate_limit_reject_abuse_without_creating_records(): void
    {
        $this->post('/embed/cfe', [...$this->publicData(), 'website' => 'spam'])->assertStatus(422);
        for ($i = 0; $i < 4; $i++) {
            $this->post('/embed/cfe', [])->assertStatus(422);
        }
        $this->post('/embed/cfe', $this->publicData())->assertStatus(429)->assertHeader('Retry-After');
        $this->assertDatabaseCount('clients', 0);
        $this->assertCount(0, Storage::disk('receipts')->allFiles());
    }

    public static function invalidFields(): array
    {
        return [
            'consent absent' => ['privacy_consent', null],
            'consent false' => ['privacy_consent', '0'],
            'stale notice' => ['privacy_version', 'old'],
            'bad email' => ['email', 'not-an-email'],
            'no name' => ['name', ''],
        ];
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_contact_and_consent_leave_no_files_or_records(string $field, mixed $value): void
    {
        $this->post('/embed/cfe', [...$this->publicData(), $field => $value])->assertStatus(422);
        $this->assertDatabaseCount('clients', 0);
        $this->assertCount(0, Storage::disk('receipts')->allFiles());
    }

    public function test_real_mime_extension_and_size_are_checked(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'helios-spoof-');
        file_put_contents($path, '<script>evil</script>');
        $spoof = new UploadedFile($path, 'fake.pdf', 'application/pdf', null, true);
        $this->post('/embed/cfe', [...$this->publicData(), 'receipt' => $spoof])->assertStatus(422);
        $this->post('/embed/cfe', [...$this->publicData(), 'receipt' => $this->file('real.php')])->assertStatus(422);
        $this->post('/embed/cfe', [...$this->publicData(), 'receipt' => UploadedFile::fake()->create('huge.pdf', 10241, 'application/pdf')])->assertStatus(422);
        $this->assertDatabaseCount('clients', 0);
    }

    public function test_crm_selects_existing_prospect_explicitly_and_authorizes_file(): void
    {
        $client = Client::factory()->create(['name' => 'Conservar']);
        $this->actingAs($this->user('sales'))->get('/intakes/create')->assertOk();
        $this->post('/intakes', [...$this->data(), 'client_id' => $client->id])->assertRedirect('/clients/'.$client->id);
        $intake = ReceiptIntake::sole();
        $this->assertSame('Conservar', $client->fresh()->name);
        $this->assertDatabaseCount('clients', 1);
        $this->get('/intakes/'.$intake->id.'/file')->assertOk()->assertDownload('recibo-'.$intake->id.'.pdf')->assertHeader('Content-Type', 'application/octet-stream');
        $this->actingAs(User::factory()->create())->get('/intakes/'.$intake->id.'/file')->assertForbidden();
        $this->actingAs($this->user('viewer'))->post('/intakes', $this->data())->assertForbidden();
    }

    public function test_engineer_can_attach_receipt_but_cannot_create_new_prospect(): void
    {
        $client = Client::factory()->create();
        $this->actingAs($this->user('engineer'))->post('/intakes', $this->data())->assertForbidden();
        $this->post('/intakes', [...$this->data(), 'client_id' => $client->id])->assertRedirect('/clients/'.$client->id);
        $this->assertDatabaseCount('receipt_intakes', 1);
        $this->assertDatabaseCount('clients', 1);
    }

    public function test_soft_deleted_prospect_cannot_be_selected(): void
    {
        $client = Client::factory()->create();
        $client->delete();
        $this->actingAs($this->user('sales'))->post('/intakes', [...$this->data(), 'client_id' => $client->id])->assertSessionHasErrors('client_id');
        $this->assertDatabaseCount('receipt_intakes', 0);
    }

    public function test_soft_deleted_prospect_receipt_cannot_be_downloaded_or_processed(): void
    {
        $client = Client::factory()->create();
        $this->actingAs($this->user('owner'))->post('/intakes', [...$this->data(), 'client_id' => $client->id])->assertRedirect('/clients/'.$client->id);
        $intake = ReceiptIntake::sole();
        $client->delete();

        $this->get('/intakes/'.$intake->id.'/file')->assertForbidden();
        $this->post('/intakes/'.$intake->id.'/mock', ['fixture_identifier' => 'complete'])->assertForbidden();
        $this->assertSame('stored', $intake->fresh()->status);
        $this->assertNull($intake->fresh()->raw_payload);
        Storage::disk('receipts')->assertExists($intake->file_path);
    }

    public function test_transaction_failure_rolls_back_new_prospect_and_removes_file(): void
    {
        DB::statement("CREATE TRIGGER fail_intake BEFORE INSERT ON receipt_intakes BEGIN SELECT RAISE(ABORT, 'test storage rollback'); END");
        try {
            app(StoreReceiptIntake::class)($this->data(), $this->file(), null, null);
            $this->fail('Expected isolated database failure');
        } catch (QueryException) {
            $this->assertDatabaseCount('clients', 0);
            $this->assertCount(0, Storage::disk('receipts')->allFiles());
        }
    }

    public static function fixtures(): array
    {
        return [['complete', 'validated'], ['partial', 'needs_review'], ['incoherent', 'rejected'], ['malformed', 'rejected']];
    }

    #[DataProvider('fixtures')]
    public function test_mock_is_explicit_uses_validators_and_keeps_raw_immutable(string $fixture, string $status): void
    {
        $this->actingAs($this->user('sales'))->post('/intakes', $this->data())->assertRedirect();
        $intake = ReceiptIntake::sole();
        $this->post('/intakes/'.$intake->id.'/mock', ['fixture_identifier' => $fixture])->assertRedirect();
        $intake->refresh();
        $this->assertSame('mock_'.$status, $intake->status);
        $this->assertSame('mock-fixture', $intake->reader_identifier);
        $this->assertSame($fixture, $intake->fixture_identifier);
        $this->assertSame(file_get_contents(resource_path('fixtures/receipts/'.$fixture.($fixture === 'malformed' ? '.txt' : '.json'))), $intake->raw_payload);
        $this->assertSame($status, $intake->validation_result['status']);
        $raw = $intake->raw_payload;
        $this->post('/intakes/'.$intake->id.'/mock', ['fixture_identifier' => 'complete'])->assertSessionHasErrors('fixture_identifier');
        $this->assertSame($raw, $intake->fresh()->raw_payload);
        $this->actingAs($this->user('viewer'))->post('/intakes/'.$intake->id.'/mock', ['fixture_identifier' => 'complete'])->assertForbidden();
    }

    public function test_frame_allowlist_cannot_inject_csp_or_wildcards(): void
    {
        config(['intake.frame_ancestors' => ['*', 'https://evil.test/path', 'https://ok.test; script-src *', 'http://plain.test', 'https://user:pass@evil.test']]);
        $this->assertSame("'none'", app(IntakeSettings::class)->frameAncestors());
        $this->get('/embed/cfe')->assertOk();
    }

    public function test_crm_keeps_csrf_protection_while_embed_does_not_use_session(): void
    {
        $this->actingAs($this->user('sales'));
        $this->app->instance('env', 'local');
        $this->withHeader('Sec-Fetch-Site', 'cross-site')->post('/intakes', $this->data())->assertStatus(419);
        $this->post('/embed/cfe', $this->publicData())->assertStatus(303);
        $this->assertDatabaseCount('receipt_intakes', 1);
    }

    public function test_missing_contact_file_and_unknown_fixture_are_rejected(): void
    {
        $data = $this->publicData();
        unset($data['email']);
        $this->post('/embed/cfe', $data)->assertStatus(422);
        $data = $this->publicData();
        unset($data['receipt']);
        $this->post('/embed/cfe', $data)->assertStatus(422);
        $this->actingAs($this->user('sales'))->post('/intakes', $this->data())->assertRedirect();
        $intake = ReceiptIntake::sole();
        $this->post('/intakes/'.$intake->id.'/mock', ['fixture_identifier' => '../../.env'])->assertSessionHasErrors('fixture_identifier');
        $this->assertNull($intake->fresh()->raw_payload);
    }

    public function test_real_png_is_accepted_with_phone_only_contact(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'helios-image-');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
        $data = $this->publicData();
        unset($data['email']);
        $this->post('/embed/cfe', [...$data, 'phone' => '+52 55 1234 5678', 'receipt' => new UploadedFile($path, 'receipt.png', 'image/png', null, true)])->assertStatus(303);
        $this->assertSame('image/png', ReceiptIntake::sole()->mime_type);
        $this->assertDatabaseCount('projects', 0);
    }

    public function test_file_download_respects_the_linked_prospect_policy(): void
    {
        $this->actingAs($this->user('sales'))->post('/intakes', $this->data())->assertRedirect();
        $intake = ReceiptIntake::sole();
        $this->mock(ClientPolicy::class, function ($mock) use ($intake): void {
            $mock->shouldReceive('view')->once()->withArgs(fn ($user, $client) => $client->id === $intake->client_id)->andReturn(false);
        });
        $this->get('/intakes/'.$intake->id.'/file')->assertForbidden();
    }
}
