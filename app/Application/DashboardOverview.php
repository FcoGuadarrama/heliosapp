<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Processing\ProcessingStatus;
use App\Models\Client;
use App\Models\SolarCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

final class DashboardOverview
{
    /** @return array<string, mixed> */
    public function forUser(User $actor): array
    {
        $viewClients = $actor->is_active && $actor->can('viewAny', Client::class);
        $viewReceipts = $viewClients && $actor->can('receipts.view');
        $cases = $this->cases();

        return [
            'metrics' => [
                'clients' => $viewClients ? Client::count() : null,
                'review' => $viewReceipts ? (clone $cases)->whereIn('processing_status', ['received', 'blocked', 'failed'])->count() : null,
                'processing' => $viewReceipts ? (clone $cases)->whereIn('processing_status', ['queued', 'running'])->count() : null,
                'quotes' => $viewReceipts ? (clone $cases)->where('processing_status', 'completed')
                    ->where('calculation_status', 'completed')->whereExists($this->currentDraft(...))->count() : null,
            ],
            'pending' => $viewReceipts ? $this->pending($cases, $actor) : [],
            'recent_clients' => $viewClients ? $this->recentClients() : [],
            'capabilities' => [
                'view_clients' => $viewClients,
                'view_receipts' => $viewReceipts,
                'create_clients' => $actor->is_active && $actor->can('create', Client::class),
                'import_receipts' => $actor->is_active && $actor->can('receipts.import'),
            ],
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /** @return Builder<SolarCase> */
    private function cases(): Builder
    {
        return SolarCase::query()->where('simulated', false)
            ->whereHas('client', fn (Builder $query) => $query->whereNull('deleted_at'));
    }

    private function currentDraft(QueryBuilder $query): void
    {
        $query->selectRaw('1')->from('equipment_quote_drafts')
            ->whereColumn('equipment_quote_drafts.solar_case_id', 'solar_cases.id')
            ->where('solar_calculation_id', function (QueryBuilder $calculation): void {
                $calculation->selectRaw('MAX(id)')->from('solar_calculations')
                    ->whereColumn('solar_calculations.solar_case_id', 'solar_cases.id');
            });
    }

    /** @param Builder<SolarCase> $cases
     * @return list<array<string, mixed>>
     */
    private function pending(Builder $cases, User $actor): array
    {
        return array_values($cases->with('client:id,name')->where(function (Builder $query): void {
            $query->whereIn('processing_status', ['received', 'blocked', 'failed'])
                ->orWhere(function (Builder $completed): void {
                    $completed->where('processing_status', 'completed')->where('calculation_status', 'completed')
                        ->whereNotExists($this->currentDraft(...));
                });
        })->orderByRaw("CASE WHEN processing_status = 'completed' THEN 1 ELSE 0 END")
            ->orderBy('created_at')->orderBy('id')->limit(6)->get()
            ->map(fn (SolarCase $case): array => [
                'id' => $case->id,
                'client_name' => $case->client->name,
                'status' => $case->processing_status->value,
                'status_label' => $this->statusLabel($case),
                'action_label' => $actor->can('process', $case) ? $this->actionLabel($case) : 'Ver expediente',
                'url' => route('clients.show', $case->client_id),
                'created_at' => $case->created_at->toIso8601String(),
            ])->all());
    }

    private function statusLabel(SolarCase $case): string
    {
        return match ($case->processing_status) {
            ProcessingStatus::Received => 'Sin analizar',
            ProcessingStatus::Failed => 'Análisis interrumpido',
            ProcessingStatus::Completed => 'Equipos por seleccionar',
            default => match ($case->blocker_code) {
                'receipt_unreadable' => 'Recibo ilegible',
                'supply_state_required' => 'Ubicación por confirmar',
                'receipt_needs_review' => 'Datos por revisar',
                default => 'Análisis pendiente de resolver',
            },
        };
    }

    private function actionLabel(SolarCase $case): string
    {
        return match ($case->processing_status) {
            ProcessingStatus::Received, ProcessingStatus::Failed => 'Revisar análisis',
            ProcessingStatus::Completed => 'Seleccionar equipos',
            default => 'Revisar expediente',
        };
    }

    /** @return list<array<string, mixed>> */
    private function recentClients(): array
    {
        return array_values(Client::query()->latest('created_at')->latest('id')->limit(5)
            ->get(['id', 'name', 'municipality', 'created_at'])
            ->map(fn (Client $client): array => [
                'id' => $client->id,
                'name' => $client->name,
                'municipality' => $client->municipality,
                'created_at' => $client->created_at->toIso8601String(),
                'url' => route('clients.show', $client),
            ])->all());
    }
}
