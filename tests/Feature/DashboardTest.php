<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\EquipmentQuoteDraft;
use App\Models\ReceiptIntake;
use App\Models\ReceiptReading;
use App\Models\SolarCalculation;
use App\Models\SolarCase;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_users_without_permissions_receive_no_operational_data(): void
    {
        $this->caseFor(Client::factory()->create(), 'blocked');

        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard')
                ->where('overview.metrics', ['clients' => null, 'review' => null, 'processing' => null, 'quotes' => null])
                ->has('overview.pending', 0)
                ->has('overview.recent_clients', 0)
                ->where('overview.capabilities', ['view_clients' => false, 'view_receipts' => false,
                    'create_clients' => false, 'import_receipts' => false]));
    }

    public function test_receipt_permission_alone_does_not_disclose_clients_or_their_cases(): void
    {
        $this->caseFor(Client::factory()->create(), 'blocked');
        $actor = User::factory()->create();
        $actor->givePermissionTo('receipts.view');

        $this->actingAs($actor)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('overview.metrics.clients', null)
                ->where('overview.metrics.review', null)
                ->where('overview.metrics.processing', null)
                ->where('overview.metrics.quotes', null)
                ->where('overview.capabilities.view_receipts', false)
                ->has('overview.pending', 0)
                ->has('overview.recent_clients', 0));
    }

    public function test_client_readers_see_recent_prospects_but_no_receipt_data(): void
    {
        $client = Client::factory()->create();
        $this->caseFor($client, 'blocked');
        $actor = User::factory()->create();
        $actor->givePermissionTo('clients.view');

        $this->actingAs($actor)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('overview.metrics.clients', 1)
                ->where('overview.metrics.review', null)
                ->where('overview.metrics.processing', null)
                ->where('overview.metrics.quotes', null)
                ->has('overview.pending', 0)
                ->has('overview.recent_clients', 1)
                ->where('overview.recent_clients.0.name', $client->name)
                ->where('overview.capabilities.create_clients', false)
                ->where('overview.capabilities.import_receipts', false));
    }

    public function test_counts_exclude_simulations_and_deleted_prospects_and_group_processing_states(): void
    {
        $client = Client::factory()->create();
        foreach (['received', 'blocked', 'failed', 'queued', 'running'] as $status) {
            $this->caseFor($client, $status);
        }
        $this->caseFor($client, 'blocked')->update(['simulated' => true]);
        $deleted = Client::factory()->create();
        $this->caseFor($deleted, 'blocked');
        $deleted->delete();

        $this->actingAs($this->viewer())->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('overview.metrics', ['clients' => 1, 'review' => 3, 'processing' => 2, 'quotes' => 0])
                ->has('overview.pending', 3)
                ->where('overview.pending.0.action_label', 'Ver expediente')
                ->has('overview.recent_clients', 1));
    }

    public function test_only_drafts_for_current_calculations_count_and_remove_pending_work(): void
    {
        $actor = $this->viewer();
        $client = Client::factory()->create();
        $currentCase = $this->caseFor($client, 'completed');
        $current = $this->calculationFor($currentCase);
        $this->draftFor($current, $actor, 1);
        $this->draftFor($current, $actor, 2);
        $staleCase = $this->caseFor($client, 'completed');
        $stale = $this->calculationFor($staleCase);
        $this->draftFor($stale, $actor, 1);
        SolarCalculation::create(['solar_case_id' => $staleCase->id,
            'receipt_reading_id' => $stale->receipt_reading_id, 'review_revision' => 2,
            'inputs' => [], 'result' => []]);

        $this->actingAs($actor)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('overview.metrics.quotes', 1)
                ->has('overview.pending', 1)
                ->where('overview.pending.0.id', $staleCase->id)
                ->where('overview.pending.0.url', route('clients.show', $client)));
    }

    public function test_pending_work_is_bounded_oldest_first_and_recent_prospects_newest_first(): void
    {
        $clients = collect();
        for ($index = 0; $index < 8; $index++) {
            $client = Client::factory()->create(['created_at' => now()->subDays(8 - $index)]);
            $clients->push($client);
            $this->caseFor($client, 'blocked')->update([
                'created_at' => now()->subDays(8 - $index),
                'attention_required_at' => now()->subDays(8 - $index),
            ]);
        }

        $this->actingAs($this->viewer())->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('overview.metrics.clients', 8)
                ->where('overview.metrics.review', 8)
                ->has('overview.pending', 6)
                ->where('overview.pending.0.client_name', $clients->first()->name)
                ->where('overview.pending.5.client_name', $clients->get(5)->name)
                ->has('overview.recent_clients', 5)
                ->where('overview.recent_clients.0.id', $clients->last()->id)
                ->where('overview.recent_clients.4.id', $clients->get(3)->id));
    }

    public function test_inactive_users_receive_no_operational_data_even_with_owner_role(): void
    {
        $this->caseFor(Client::factory()->create(), 'blocked');
        $actor = User::factory()->create(['is_active' => false]);
        $actor->assignRole('owner');

        $this->actingAs($actor)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('overview.metrics', ['clients' => null, 'review' => null, 'processing' => null, 'quotes' => null])
                ->has('overview.pending', 0)
                ->has('overview.recent_clients', 0)
                ->where('overview.capabilities.create_clients', false)
                ->where('overview.capabilities.import_receipts', false));
    }

    public function test_actionable_attention_precedes_older_equipment_selection_for_operators(): void
    {
        $client = Client::factory()->create();
        $completed = $this->caseFor($client, 'completed');
        $completed->update(['created_at' => now()->subDays(3)]);
        $this->calculationFor($completed);
        $blocked = $this->caseFor($client, 'blocked');
        $blocked->update(['blocker_code' => 'supply_state_required']);
        $actor = User::factory()->create();
        $actor->assignRole('owner');

        $this->actingAs($actor)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('overview.pending', 2)
                ->where('overview.pending.0.id', $blocked->id)
                ->where('overview.pending.0.status_label', 'Ubicación por confirmar')
                ->where('overview.pending.1.id', $completed->id)
                ->where('overview.pending.1.action_label', 'Seleccionar equipos')
                ->where('overview.capabilities.create_clients', true)
                ->where('overview.capabilities.import_receipts', true));
    }

    private function viewer(): User
    {
        $actor = User::factory()->create();
        $actor->assignRole('viewer');

        return $actor;
    }

    private function caseFor(Client $client, string $status): SolarCase
    {
        return SolarCase::create(['public_id' => (string) Str::uuid(), 'client_id' => $client->id,
            'processing_status' => $status, 'calculation_status' => $status === 'completed' ? 'completed' : 'pending']);
    }

    private function calculationFor(SolarCase $case): SolarCalculation
    {
        $intake = ReceiptIntake::create(['solar_case_id' => $case->id, 'client_id' => $case->client_id,
            'source' => 'crm', 'file_path' => 'synthetic.pdf', 'mime_type' => 'application/pdf',
            'file_size' => 100, 'sha256' => str_repeat('a', 64), 'privacy_version' => 'test',
            'privacy_text' => 'Test', 'consented_at' => now()]);
        $reading = ReceiptReading::create(['receipt_intake_id' => $intake->id, 'model' => 'test',
            'prompt_version' => 'test', 'page_count' => 1, 'raw_response' => '{}',
            'payload_json' => '{}', 'validation_result' => [], 'status' => 'reviewed']);

        return SolarCalculation::create(['solar_case_id' => $case->id,
            'receipt_reading_id' => $reading->id, 'review_revision' => 1,
            'inputs' => [], 'result' => []]);
    }

    private function draftFor(SolarCalculation $calculation, User $actor, int $version): void
    {
        EquipmentQuoteDraft::create(['solar_case_id' => $calculation->solar_case_id,
            'solar_calculation_id' => $calculation->id, 'created_by' => $actor->id,
            'submission_id' => (string) Str::uuid(), 'version' => $version, 'snapshot' => []]);
    }
}
