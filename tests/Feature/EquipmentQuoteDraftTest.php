<?php

namespace Tests\Feature;

use App\Application\StoreReceiptIntake;
use App\Models\EquipmentQuoteDraft;
use App\Models\ReceiptIntake;
use App\Models\ReceiptReading;
use App\Models\ReceiptReview;
use App\Models\SolarCalculation;
use App\Models\SolarCase;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class EquipmentQuoteDraftTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private ReceiptIntake $intake;

    private SolarCalculation $calculation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actor = User::factory()->create();
        $this->actor->assignRole('owner');
        Storage::fake('receipts');
        Http::preventStrayRequests();
        config(['intake.privacy_version' => 'test', 'intake.privacy_text' => 'test',
            'services.syscom.client_id' => 'synthetic', 'services.syscom.client_secret' => 'synthetic', 'quotes.vat_rate' => null]);
        $file = UploadedFile::fake()->createWithContent('test.pdf', file_get_contents(resource_path('fixtures/receipts/synthetic-cfe-source.pdf')));
        $this->intake = app(StoreReceiptIntake::class)(['name' => 'Synthetic', 'email' => 'synthetic@example.test'], $file, null, $this->actor);
        $reading = ReceiptReading::create(['receipt_intake_id' => $this->intake->id, 'model' => 'test',
            'prompt_version' => 'test', 'page_count' => 1, 'raw_response' => '{}', 'payload_json' => '{}',
            'validation_result' => [], 'normalization' => [], 'status' => 'needs_review']);
        ReceiptReview::create(['receipt_reading_id' => $reading->id, 'created_by' => $this->actor->id,
            'revision' => 1, 'reason' => 'Synthetic review', 'payload_json' => '{}', 'validation_result' => [], 'normalization' => []]);
        $this->calculation = SolarCalculation::create(['solar_case_id' => $this->intake->solar_case_id,
            'receipt_reading_id' => $reading->id, 'review_revision' => 1, 'inputs' => [], 'result' => []]);
        SolarCase::whereKey($this->intake->solar_case_id)->update(['calculation_status' => 'completed']);
    }

    private function provider(): void
    {
        Http::fake(['*/oauth/token' => Http::response(['access_token' => 'synthetic', 'token_type' => 'Bearer']),
            '*/productos*' => Http::response(['producto_id' => '123', 'modelo' => 'TEST-123', 'titulo' => 'Synthetic equipment',
                'precios' => ['precio_lista' => '40.0050', 'precio_descuento' => '10.00'], 'total_existencia' => 2])]);
    }

    private function data(): array
    {
        return ['calculation_id' => $this->calculation->id, 'submission_id' => (string) Str::uuid(), 'confirmed' => true,
            'lines' => [['product_id' => '123', 'quantity' => 3, 'unit_price_cents' => 1]]];
    }

    public function test_draft_freezes_provider_list_price_not_client_price_and_never_assumes_tax_zero(): void
    {
        $this->provider();
        $data = $this->data();
        $this->actingAs($this->actor)->post('/intakes/'.$this->intake->id.'/quote-drafts', $data)->assertRedirect()->assertSessionHasNoErrors();
        $draft = EquipmentQuoteDraft::sole();
        $this->assertSame(12003, $draft->snapshot['subtotal_cents']);
        $this->assertNull($draft->snapshot['vat_cents']);
        $this->assertNull($draft->snapshot['total_cents']);
        $this->assertSame('insufficient', $draft->snapshot['lines'][0]['availability']);
        $this->assertSame('pending', $draft->snapshot['compatibility_status']);
        $this->assertFalse($draft->snapshot['installation_included']);
        $this->actingAs($this->actor)->post('/intakes/'.$this->intake->id.'/quote-drafts', $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseCount('equipment_quote_drafts', 1);
        Http::assertSentCount(2);
    }

    public function test_explicit_tax_is_applied_once_and_each_save_creates_a_frozen_version(): void
    {
        $this->provider();
        config(['quotes.vat_rate' => '16.00']);
        $this->actingAs($this->actor)->post('/intakes/'.$this->intake->id.'/quote-drafts', $this->data())->assertRedirect()->assertSessionHasNoErrors();
        $draft = EquipmentQuoteDraft::sole();
        $this->assertSame(1920, $draft->snapshot['vat_cents']);
        $this->assertSame(13923, $draft->snapshot['total_cents']);
        config(['quotes.vat_rate' => null]);
        $this->actingAs($this->actor)->post('/intakes/'.$this->intake->id.'/quote-drafts', $this->data())->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(13923, $draft->fresh()->snapshot['total_cents']);
        $this->assertSame(2, EquipmentQuoteDraft::max('version'));
    }

    public function test_changed_review_blocks_quote_from_previous_calculation(): void
    {
        $this->provider();
        $review = ReceiptReview::sole()->replicate();
        $review->revision = 2;
        $review->save();
        $this->actingAs($this->actor)->post('/intakes/'.$this->intake->id.'/quote-drafts', $this->data())
            ->assertSessionHasErrors('quote');
        $this->assertDatabaseCount('equipment_quote_drafts', 0);
    }

    public function test_inactive_user_cannot_query_prices_or_save_drafts(): void
    {
        $this->actor->update(['is_active' => false]);
        $this->actingAs($this->actor)->getJson('/intakes/'.$this->intake->id.'/catalog?query=panel')->assertForbidden();
        $this->actingAs($this->actor)->post('/intakes/'.$this->intake->id.'/quote-drafts', $this->data())->assertForbidden();
        Http::assertNothingSent();
    }
}
