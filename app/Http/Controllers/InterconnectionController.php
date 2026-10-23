<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Solar\InterconnectionCalculator;
use App\Http\Requests\CalculateInterconnectionRequest;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final class InterconnectionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Engineering/Interconnection', ['result' => null]);
    }

    public function calculate(CalculateInterconnectionRequest $request, InterconnectionCalculator $calculator): Response
    {
        try {
            $result = $calculator->calculate($request->inputData());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['current_kwh' => $exception->getMessage()]);
        }

        return Inertia::render('Engineering/Interconnection', ['result' => $result->toArray()]);
    }
}
