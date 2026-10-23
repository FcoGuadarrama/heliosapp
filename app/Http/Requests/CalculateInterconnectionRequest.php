<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Solar\InterconnectionInput;
use Illuminate\Foundation\Http\FormRequest;

final class CalculateInterconnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('receipts.import') ?? false;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'current_kwh' => ['required', 'numeric', 'between:0,1000000'],
            'period_days' => ['required', 'integer', 'between:1,366'],
            'hsp' => ['required', 'numeric', 'between:0.1,24'],
            'module_w' => ['required', 'numeric', 'between:1,10000'],
            'history_kwh' => ['present', 'array', 'list', 'max:24'],
            'history_kwh.*' => ['required', 'numeric', 'between:0,1000000'],
            'modules_per_micro' => ['nullable', 'integer', 'between:1,100'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'current_kwh' => 'consumo del período',
            'period_days' => 'días del período',
            'hsp' => 'horas de sol pico',
            'module_w' => 'potencia del panel',
            'history_kwh.*' => 'consumo histórico',
            'modules_per_micro' => 'módulos por microinversor',
        ];
    }

    public function inputData(): InterconnectionInput
    {
        /** @var list<int|float|string> $history */
        $history = $this->validated('history_kwh');

        return new InterconnectionInput(
            currentConsumptionKwh: (float) $this->validated('current_kwh'),
            periodDays: (int) $this->validated('period_days'),
            hsp: (float) $this->validated('hsp'),
            modulePowerW: (float) $this->validated('module_w'),
            historyKwh: array_map(fn (int|float|string $value): float => (float) $value, $history),
            modulesPerMicroinverter: $this->validated('modules_per_micro') === null
                ? null
                : (int) $this->validated('modules_per_micro'),
        );
    }
}
