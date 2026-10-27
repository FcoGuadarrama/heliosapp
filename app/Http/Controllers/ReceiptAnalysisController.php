<?php

namespace App\Http\Controllers;

use App\Application\ReceiveReceipt;
use App\Application\ReviewReceiptReading;
use App\Application\StoreSecondReceipt;
use App\Http\Resources\ReceiptAnalysis;
use App\Infrastructure\Hsp\StateGrid;
use App\Models\ReceiptIntake;
use App\Models\ReceiptReading;
use App\Models\ReceiptReview;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class ReceiptAnalysisController extends Controller
{
    use AuthorizesRequests;

    public function show(Request $request, ReceiptIntake $intake, ReceiptAnalysis $resource): JsonResponse
    {
        return response()->json($resource->forAdvisor($intake, $request->user()))->header('Cache-Control', 'no-store, private');
    }

    public function retry(Request $request, ReceiptIntake $intake, ReceiveReceipt $receive): RedirectResponse
    {
        $this->authorize('process', $intake);
        abort_unless(config('receipt_reader.enabled'), 503, 'Lector no habilitado.');
        $receive->enqueue($intake, $request->user());

        return back()->with('success', 'Se solicitó el análisis del recibo.');
    }

    public function review(Request $request, ReceiptIntake $intake, StateGrid $states, ReviewReceiptReading $review): RedirectResponse
    {
        $this->authorize('process', $intake);
        $data = $request->validate([
            'revision' => 'required|integer|min:0', 'reason' => 'required|string|min:10|max:1000',
            'confirmed' => 'required|accepted', 'tariff_code' => 'required|string|max:16',
            'consumption_kwh' => 'required|numeric|min:0', 'period_start' => 'required|date_format:Y-m-d',
            'phases' => 'nullable|integer|in:1,2,3', 'days' => 'nullable|integer|min:1|max:75',
            'period_end' => 'required|date_format:Y-m-d|after:period_start',
            'state_code' => ['required', 'string', Rule::in(array_map(fn ($state): string => $state->code, $states->states))],
            'history' => 'required|array|max:36', 'history.*' => 'array',
            'history.*.period_start' => 'nullable|date_format:Y-m-d', 'history.*.period_end' => 'nullable|date_format:Y-m-d',
            'history.*.consumption_kwh' => 'nullable|numeric|min:0', 'history.*.amount_mxn' => 'nullable|numeric|min:0',
            'history.*.days' => 'nullable|integer|min:1|max:75',
            'concepts' => 'required|array|max:36', 'concepts.*' => 'array', 'concepts.*.code' => 'nullable|string|max:100',
            'concepts.*.description' => 'nullable|string|max:200', 'concepts.*.amount_mxn' => 'nullable|numeric|min:0',
            'period_amount_mxn' => 'nullable|numeric|min:0', 'total_amount_mxn' => 'nullable|numeric|min:0',
        ]);
        $reading = ReceiptReading::where('receipt_intake_id', $intake->id)->firstOrFail();
        $latest = ReceiptReview::where('receipt_reading_id', $reading->id)->latest('revision')->first();
        $payload = json_decode($latest->payload_json ?? $reading->payload_json, true, flags: JSON_THROW_ON_ERROR);
        $payload['service']['tariff_code'] = $data['tariff_code'];
        $payload['service']['phases'] = ($data['phases'] ?? null) === null ? null : (int) $data['phases'];
        foreach ($states->states as $state) {
            if ($state->code === $data['state_code']) {
                $payload['service']['service_address']['state'] = $state->name;
            }
        }
        $payload['current_period']['consumption_kwh'] = (float) $data['consumption_kwh'];
        $payload['current_period']['period_start'] = $data['period_start'];
        $payload['current_period']['period_end'] = $data['period_end'];
        $payload['current_period']['days'] = ($data['days'] ?? null) === null ? null : (int) $data['days'];
        $payload['current_period']['amount_mxn'] = ($data['period_amount_mxn'] ?? null) === null ? null : (float) $data['period_amount_mxn'];
        $payload['receipt']['total_amount_mxn'] = ($data['total_amount_mxn'] ?? null) === null ? null : (float) $data['total_amount_mxn'];
        $payload['current_period']['concepts'] = array_map(fn (array $row): array => [
            'code' => $row['code'] ?? null, 'description' => $row['description'] ?? null,
            'amount_mxn' => ($row['amount_mxn'] ?? null) === null ? null : (float) $row['amount_mxn'],
        ], $data['concepts']);
        $payload['consumption_history'] = array_map(fn (array $row): array => [
            'period_start' => $row['period_start'] ?? null, 'period_end' => $row['period_end'] ?? null,
            'days' => ($row['days'] ?? null) === null ? null : (int) $row['days'],
            'consumption_kwh' => ($row['consumption_kwh'] ?? null) === null ? null : (float) $row['consumption_kwh'],
            'amount_mxn' => ($row['amount_mxn'] ?? null) === null ? null : (float) $row['amount_mxn'], 'source' => 'receipt_table',
        ], $data['history']);
        $payload['extraction']['status'] = 'partial';
        $review($reading, $request->user(), json_encode($payload, JSON_THROW_ON_ERROR), $data['reason'], (int) $data['revision']);
        app(ReceiveReceipt::class)->enqueue($intake, $request->user());

        return back()->with('success', 'Datos confirmados. Se continuará el análisis.');
    }

    public function replace(Request $request, ReceiptIntake $intake, StoreSecondReceipt $store, ReceiveReceipt $receive): RedirectResponse
    {
        $this->authorize('process', $intake);
        $request->validate(['receipt' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:'.config('intake.max_file_kb')]);
        $file = $request->file('receipt');
        abort_unless($file instanceof UploadedFile, 422);
        $replacement = $store($intake, $file, $request->user());
        $receive->enqueue($replacement);

        return back()->with('success', 'Segunda carga recibida.');
    }
}
