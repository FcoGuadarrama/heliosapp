<?php

namespace App\Http\Controllers;

use App\Application\ReceiveReceipt;
use App\Application\ValidateMockReceipt;
use App\Domain\Intake\IntakeSettings;
use App\Domain\Intake\MockReceiptReader;
use App\Http\Requests\StoreIntakeRequest;
use App\Models\Client;
use App\Models\ReceiptIntake;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntakeController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request, IntakeSettings $settings): Response
    {
        $this->authorize('create', ReceiptIntake::class);
        $this->authorize('viewAny', Client::class);
        $url = route('embed.cfe');
        $selectedClientId = $request->integer('client_id');
        if ($selectedClientId !== 0) {
            $client = Client::whereKey($selectedClientId)->firstOrFail();
            $this->authorize('view', $client);
        }

        return Inertia::render('Intakes/Create', [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'privacyNotice' => $settings->notice(),
            'intakeEnabled' => $settings->available(),
            'selectedClientId' => $selectedClientId === 0 ? null : $selectedClientId,
            'maxFileKb' => (int) config('intake.max_file_kb'),
            'embed' => [
                'url' => $url,
                'frameAncestors' => $settings->frameAncestors(),
                'snippet' => '<iframe src="'.htmlspecialchars($url, ENT_QUOTES).'" title="Enviar recibo CFE a Helios" width="100%" height="740" referrerpolicy="no-referrer"></iframe>',
            ],
        ]);
    }

    public function store(StoreIntakeRequest $request, IntakeSettings $settings, ReceiveReceipt $store): RedirectResponse
    {
        abort_unless($settings->available(), 503, 'Recepción no configurada.');
        $this->authorize('create', ReceiptIntake::class);
        $data = $request->validated();
        $client = empty($data['client_id']) ? null : Client::whereKey($request->integer('client_id'))->firstOrFail();
        $this->authorize($client === null ? 'create' : 'view', $client ?? Client::class);
        $file = $request->file('receipt');
        abort_unless($file instanceof UploadedFile, 422);
        $intake = $store($data, $file, $client, $request->user());

        return to_route('clients.show', $intake->client_id)->with('success', config('receipt_reader.enabled')
            ? 'Recibo recibido. El estado del análisis aparece en el prospecto.' : 'Recibo guardado. El lector está deshabilitado.');
    }

    public function download(ReceiptIntake $intake): StreamedResponse
    {
        $this->authorize('view', $intake);
        abort_unless(Storage::disk('receipts')->exists($intake->file_path), 404);
        $extension = match ($intake->mime_type) {
            'application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
            default => 'bin',
        };

        return Storage::disk('receipts')->download($intake->file_path, 'recibo-'.$intake->id.'.'.$extension, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function mock(Request $request, ReceiptIntake $intake, ValidateMockReceipt $validate): RedirectResponse
    {
        $this->authorize('process', $intake);
        $data = $request->validate(['fixture_identifier' => ['required', 'string', Rule::in(MockReceiptReader::FIXTURES)]]);
        $validate($intake, $data['fixture_identifier']);

        return to_route('clients.show', $intake->client_id)->with('success', 'Simulación validada. Los datos son ficticios; no fueron extraídos del archivo.');
    }
}
