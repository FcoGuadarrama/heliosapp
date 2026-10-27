<?php

namespace App\Http\Controllers;

use App\Application\ReceiveReceipt;
use App\Domain\Intake\IntakeSettings;
use App\Domain\Intake\PublicFormToken;
use App\Http\Requests\StoreIntakeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class EmbedIntakeController extends Controller
{
    public function show(Request $request, IntakeSettings $settings, PublicFormToken $tokens): Response
    {
        abort_unless($settings->available(), 503, 'Recepción no configurada.');

        return response()->view('embed.cfe', [
            'formToken' => $tokens->issue(),
            'privacyNotice' => $settings->notice(),
            'submitted' => $request->query('submitted') === '1',
            'errors' => [],
        ]);
    }

    public function store(Request $request, IntakeSettings $settings, PublicFormToken $tokens, ReceiveReceipt $store): Response|RedirectResponse
    {
        abort_unless($settings->available(), 503, 'Recepción no configurada.');
        $validator = Validator::make($request->all(), [
            ...StoreIntakeRequest::intakeRules(false),
            'website' => ['nullable', 'string', 'max:0'],
            'form_token' => ['required', 'string', 'max:4096'],
        ]);
        if ($validator->fails()) {
            return response()->view('embed.cfe', [
                'formToken' => $tokens->issue(),
                'privacyNotice' => $settings->notice(),
                'submitted' => false,
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }
        abort_unless($tokens->consume($request->string('form_token')->toString()), 422, 'Formulario vencido o ya enviado. Recarga e intenta de nuevo.');
        $file = $request->file('receipt');
        abort_unless($file instanceof UploadedFile, 422);
        // Never resolve an authenticated session or existing prospect on this route.
        $store($validator->validated(), $file, null, null);

        return redirect()->route('embed.cfe', ['submitted' => 1], 303);
    }
}
