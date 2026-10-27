<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use App\Domain\ReceiptData\DTO\ConceptData;
use App\Domain\ReceiptData\DTO\ReceiptPayload;
use App\Domain\ReceiptData\DTO\TierData;
use App\Domain\ReceiptData\Enums\RuleCode;
use App\Domain\ReceiptData\Support\DecimalComparator;
use App\Domain\ReceiptData\ValueObjects\RuleViolation;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * Validación de COHERENCIA del JSON de recibo CFE: contrato §4.
 *
 * Opera sobre un {@see ReceiptPayload} ya construido (estructuralmente
 * tolerante) e implementa 16 de las 18 reglas de la tabla. Las otras 2
 * (`MALFORMED_JSON`, `UNSUPPORTED_SCHEMA_VERSION`) viven en
 * {@see ReceiptSchemaValidator} porque dependen del cuerpo crudo, antes de
 * que exista un payload.
 *
 * Ningún umbral está incrustado aquí: todos se leen de
 * `config('helios.receipts')`.
 */
final class ReceiptCoherenceValidator
{
    /**
     * @return list<RuleViolation>
     */
    public function validate(ReceiptPayload $payload): array
    {
        $violations = [];

        $this->checkNegativeConsumption($payload, $violations);
        $this->checkNegativeAmount($payload, $violations);

        $periodStart = self::parseDate($payload->currentPeriod->periodStart);
        $periodEnd = self::parseDate($payload->currentPeriod->periodEnd);

        $this->checkInvalidPeriod($periodStart, $periodEnd, $violations);
        $this->checkFuturePeriod($periodEnd, $violations);
        $this->checkImpossiblePeriodLength($periodStart, $periodEnd, $violations);
        $this->checkPeriodDaysMismatch($payload, $periodStart, $periodEnd, $violations);

        $this->checkTierSumMismatch($payload, $violations);
        $this->checkConceptSumMismatch($payload, $violations);
        $this->checkTotalMismatch($payload, $violations);

        $this->checkOverlappingHistory($payload, $violations);
        $this->checkHistoryGap($payload, $violations);
        $this->checkConsumptionOutlier($payload, $violations);

        $this->checkUnknownTariff($payload, $violations);
        $this->checkMissingCriticalField($payload, $violations);
        $this->checkUnknownField($payload, $violations);
        $this->checkLowConfidence($payload, $violations);

        return $violations;
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkNegativeConsumption(ReceiptPayload $payload, array &$violations): void
    {
        if (DecimalComparator::isNegative($payload->currentPeriod->consumptionKwh)) {
            $violations[] = RuleViolation::make(
                RuleCode::NegativeConsumption,
                'El consumo del periodo actual no puede ser negativo.',
                'current_period.consumption_kwh',
            );
        }

        foreach ($payload->currentPeriod->consumptionByTier as $i => $tier) {
            if (DecimalComparator::isNegative($tier->kwh)) {
                $violations[] = RuleViolation::make(
                    RuleCode::NegativeConsumption,
                    "El consumo del escalón {$i} no puede ser negativo.",
                    "current_period.consumption_by_tier[{$i}].kwh",
                );
            }
        }

        foreach ($payload->consumptionHistory as $i => $period) {
            if (DecimalComparator::isNegative($period->consumptionKwh)) {
                $violations[] = RuleViolation::make(
                    RuleCode::NegativeConsumption,
                    "El consumo del periodo histórico {$i} no puede ser negativo.",
                    "consumption_history[{$i}].consumption_kwh",
                );
            }
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkNegativeAmount(ReceiptPayload $payload, array &$violations): void
    {
        $checks = [
            'receipt.total_amount_mxn' => $payload->receipt->totalAmountMxn,
            'current_period.amount_mxn' => $payload->currentPeriod->amountMxn,
        ];

        foreach ($checks as $field => $value) {
            if (DecimalComparator::isNegative($value)) {
                $violations[] = RuleViolation::make(
                    RuleCode::NegativeAmount,
                    'El importe de "'.$field.'" no puede ser negativo.',
                    $field,
                );
            }
        }

        foreach ($payload->currentPeriod->consumptionByTier as $i => $tier) {
            if (DecimalComparator::isNegative($tier->unitPriceMxn)) {
                $violations[] = RuleViolation::make(
                    RuleCode::NegativeAmount,
                    "El precio unitario del escalón {$i} no puede ser negativo.",
                    "current_period.consumption_by_tier[{$i}].unit_price_mxn",
                );
            }

            if (DecimalComparator::isNegative($tier->amountMxn)) {
                $violations[] = RuleViolation::make(
                    RuleCode::NegativeAmount,
                    "El importe del escalón {$i} no puede ser negativo.",
                    "current_period.consumption_by_tier[{$i}].amount_mxn",
                );
            }
        }

        foreach ($payload->currentPeriod->concepts as $i => $concept) {
            if (DecimalComparator::isNegative($concept->amountMxn)) {
                $violations[] = RuleViolation::make(
                    RuleCode::NegativeAmount,
                    "El importe del concepto {$i} no puede ser negativo.",
                    "current_period.concepts[{$i}].amount_mxn",
                );
            }
        }

        foreach ($payload->consumptionHistory as $i => $period) {
            if (DecimalComparator::isNegative($period->amountMxn)) {
                $violations[] = RuleViolation::make(
                    RuleCode::NegativeAmount,
                    "El importe del periodo histórico {$i} no puede ser negativo.",
                    "consumption_history[{$i}].amount_mxn",
                );
            }
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkInvalidPeriod(?CarbonImmutable $start, ?CarbonImmutable $end, array &$violations): void
    {
        if ($start === null || $end === null) {
            return;
        }

        if ($end->lessThanOrEqualTo($start)) {
            $violations[] = RuleViolation::make(
                RuleCode::InvalidPeriod,
                'La fecha de fin del periodo debe ser posterior a la fecha de inicio.',
                'current_period.period_end',
            );
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkFuturePeriod(?CarbonImmutable $end, array &$violations): void
    {
        if ($end === null) {
            return;
        }

        if ($end->greaterThan(CarbonImmutable::now())) {
            $violations[] = RuleViolation::make(
                RuleCode::FuturePeriod,
                'La fecha de fin del periodo no puede ser posterior a hoy.',
                'current_period.period_end',
            );
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkImpossiblePeriodLength(?CarbonImmutable $start, ?CarbonImmutable $end, array &$violations): void
    {
        if ($start === null || $end === null) {
            return;
        }

        $days = abs($end->diffInDays($start));

        $min = (int) config('helios.receipts.period_min_days', 20);
        $max = (int) config('helios.receipts.period_max_days', 75);

        if ($days < $min || $days > $max) {
            $violations[] = RuleViolation::make(
                RuleCode::ImpossiblePeriodLength,
                "La duración del periodo ({$days} días) está fuera del rango válido ({$min}-{$max} días).",
                'current_period.period_end',
            );
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkPeriodDaysMismatch(ReceiptPayload $payload, ?CarbonImmutable $start, ?CarbonImmutable $end, array &$violations): void
    {
        $declaredDays = $payload->currentPeriod->days;

        if ($declaredDays === null || $start === null || $end === null) {
            return;
        }

        $computedDays = abs($end->diffInDays($start));
        $tolerance = (int) config('helios.receipts.period_days_mismatch_tolerance_days', 2);

        if (abs((float) $declaredDays - $computedDays) > $tolerance) {
            $violations[] = RuleViolation::make(
                RuleCode::PeriodDaysMismatch,
                "El campo \"days\" ({$declaredDays}) difiere en más de {$tolerance} días de la duración calculada ({$computedDays}).",
                'current_period.days',
            );
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkTierSumMismatch(ReceiptPayload $payload, array &$violations): void
    {
        if ($payload->currentPeriod->consumptionByTier === [] || $payload->currentPeriod->consumptionKwh === null) {
            return;
        }

        $sum = self::sumDecimal(array_map(fn (TierData $tier): int|float|null => $tier->kwh, $payload->currentPeriod->consumptionByTier));

        if ($sum === null) {
            return;
        }

        $tolerance = (float) config('helios.receipts.amount_tolerance_percent', 0.01);

        if (DecimalComparator::relativeDifferenceExceeds($sum, $payload->currentPeriod->consumptionKwh, $tolerance) === true) {
            $violations[] = RuleViolation::make(
                RuleCode::TierSumMismatch,
                'La suma de los escalones de consumo no coincide con el consumo total del periodo.',
                'current_period.consumption_by_tier',
            );
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkConceptSumMismatch(ReceiptPayload $payload, array &$violations): void
    {
        if ($payload->currentPeriod->concepts === [] || $payload->currentPeriod->amountMxn === null) {
            return;
        }

        $sum = self::sumDecimal(array_map(fn (ConceptData $concept): int|float|null => $concept->amountMxn, $payload->currentPeriod->concepts));

        if ($sum === null) {
            return;
        }

        $tolerance = (float) config('helios.receipts.amount_tolerance_percent', 0.01);

        if (DecimalComparator::relativeDifferenceExceeds($sum, $payload->currentPeriod->amountMxn, $tolerance) === true) {
            $violations[] = RuleViolation::make(
                RuleCode::ConceptSumMismatch,
                'La suma de los conceptos no coincide con el importe del periodo.',
                'current_period.concepts',
            );
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkTotalMismatch(ReceiptPayload $payload, array &$violations): void
    {
        $tolerance = (float) config('helios.receipts.amount_tolerance_percent', 0.01);

        if (DecimalComparator::relativeDifferenceExceeds(
            $payload->receipt->totalAmountMxn,
            $payload->currentPeriod->amountMxn,
            $tolerance,
        ) === true) {
            $violations[] = RuleViolation::make(
                RuleCode::TotalMismatch,
                'El total del recibo no coincide con el importe del periodo actual.',
                'receipt.total_amount_mxn',
            );
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkOverlappingHistory(ReceiptPayload $payload, array &$violations): void
    {
        $periods = self::datedHistoryPeriods($payload);

        $count = count($periods);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $a = $periods[$i];
                $b = $periods[$j];

                $overlaps = $a['start']->lessThan($b['end']) && $b['start']->lessThan($a['end']);

                if ($overlaps) {
                    $violations[] = RuleViolation::make(
                        RuleCode::OverlappingHistory,
                        "Los periodos históricos {$a['index']} y {$b['index']} se traslapan.",
                        'consumption_history',
                    );

                    return;
                }
            }
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkHistoryGap(ReceiptPayload $payload, array &$violations): void
    {
        $periods = self::datedHistoryPeriods($payload);

        usort($periods, fn (array $a, array $b): int => $a['start']->timestamp <=> $b['start']->timestamp);

        $maxGap = (int) config('helios.receipts.history_gap_max_days', 10);

        for ($i = 0; $i < count($periods) - 1; $i++) {
            $current = $periods[$i];
            $next = $periods[$i + 1];

            $gap = abs($next['start']->diffInDays($current['end']));

            if ($next['start']->greaterThan($current['end']) && $gap > $maxGap) {
                $violations[] = RuleViolation::make(
                    RuleCode::HistoryGap,
                    "Hay un hueco de {$gap} días entre los periodos históricos {$current['index']} y {$next['index']}.",
                    'consumption_history',
                );
            }
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkConsumptionOutlier(ReceiptPayload $payload, array &$violations): void
    {
        $entries = [];

        foreach ($payload->consumptionHistory as $i => $period) {
            if ($period->consumptionKwh !== null) {
                $entries[] = ['index' => $i, 'value' => (float) $period->consumptionKwh];
            }
        }

        if (count($entries) < 3) {
            return;
        }

        $sigmaThreshold = (float) config('helios.receipts.consumption_outlier_sigma', 3);

        foreach ($entries as $entry) {
            $rest = array_values(array_map(
                fn (array $other): float => $other['value'],
                array_filter($entries, fn (array $other): bool => $other['index'] !== $entry['index']),
            ));

            $mean = array_sum($rest) / count($rest);
            $variance = array_sum(array_map(fn (float $v): float => ($v - $mean) ** 2, $rest)) / count($rest);
            $stdDev = sqrt($variance);

            $isOutlier = $stdDev > 0.0
                ? abs($entry['value'] - $mean) / $stdDev > $sigmaThreshold
                : abs($entry['value'] - $mean) > 0.0;

            if ($isOutlier) {
                $violations[] = RuleViolation::make(
                    RuleCode::ConsumptionOutlier,
                    "El consumo del periodo histórico {$entry['index']} se desvía más de {$sigmaThreshold}σ del resto del historial.",
                    "consumption_history[{$entry['index']}].consumption_kwh",
                );
            }
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkUnknownTariff(ReceiptPayload $payload, array &$violations): void
    {
        $tariffCode = $payload->service->tariffCode;

        if ($tariffCode === null || $tariffCode === '') {
            return;
        }

        $known = config('helios.receipts.known_tariff_codes', []);
        $normalized = array_map(fn (string $code): string => strtoupper(trim($code)), $known);

        if (! in_array(strtoupper(trim($tariffCode)), $normalized, true)) {
            $violations[] = RuleViolation::make(
                RuleCode::UnknownTariff,
                "La clave de tarifa \"{$tariffCode}\" no está en el catálogo de tarifas conocidas.",
                'service.tariff_code',
            );
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkMissingCriticalField(ReceiptPayload $payload, array &$violations): void
    {
        $criticalFields = config('helios.receipts.critical_fields', []);

        $resolvers = [
            'current_period.consumption_kwh' => fn (): mixed => $payload->currentPeriod->consumptionKwh,
            'current_period.period_start' => fn (): mixed => $payload->currentPeriod->periodStart,
            'current_period.period_end' => fn (): mixed => $payload->currentPeriod->periodEnd,
            'service.tariff_code' => fn (): mixed => $payload->service->tariffCode,
        ];

        foreach ($criticalFields as $field) {
            if (! isset($resolvers[$field])) {
                continue;
            }

            if ($resolvers[$field]() === null) {
                $violations[] = RuleViolation::make(
                    RuleCode::MissingCriticalField,
                    "Falta el campo crítico \"{$field}\": no es posible producir un diagnóstico energético sin él.",
                    $field,
                );
            }
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkUnknownField(ReceiptPayload $payload, array &$violations): void
    {
        foreach (self::collectUnknownFieldPaths($payload) as $path) {
            $violations[] = RuleViolation::make(
                RuleCode::UnknownField,
                "El campo \"{$path}\" no está contemplado por el esquema v1.0. Se conserva íntegro.",
                $path,
            );
        }
    }

    /**
     * @param  list<RuleViolation>  $violations
     */
    private function checkLowConfidence(ReceiptPayload $payload, array &$violations): void
    {
        $criticalFields = config('helios.receipts.critical_fields', []);
        $threshold = (float) config('helios.receipts.low_confidence_threshold', 0.70);

        foreach ($payload->extraction->confidence as $field => $confidence) {
            if (! in_array($field, $criticalFields, true)) {
                continue;
            }

            if (! is_numeric($confidence)) {
                continue;
            }

            if ((float) $confidence < $threshold) {
                $violations[] = RuleViolation::make(
                    RuleCode::LowConfidence,
                    "La confianza del campo crítico \"{$field}\" ({$confidence}) es menor al umbral ({$threshold}).",
                    (string) $field,
                );
            }
        }
    }

    /**
     * @return list<array{index: int, start: CarbonImmutable, end: CarbonImmutable}>
     */
    private static function datedHistoryPeriods(ReceiptPayload $payload): array
    {
        $periods = [];

        foreach ($payload->consumptionHistory as $i => $period) {
            $start = self::parseDate($period->periodStart);
            $end = self::parseDate($period->periodEnd);

            if ($start !== null && $end !== null && $end->greaterThanOrEqualTo($start)) {
                $periods[] = ['index' => $i, 'start' => $start, 'end' => $end];
            }
        }

        return $periods;
    }

    /**
     * @return list<string>
     */
    private static function collectUnknownFieldPaths(ReceiptPayload $payload): array
    {
        $paths = [];

        self::appendExtraPaths($payload->extra, '', $paths);
        self::appendExtraPaths($payload->document->extra, 'document', $paths);
        self::appendExtraPaths($payload->document->extractor->extra, 'document.extractor', $paths);
        self::appendExtraPaths($payload->receipt->extra, 'receipt', $paths);
        self::appendExtraPaths($payload->service->extra, 'service', $paths);
        self::appendExtraPaths($payload->service->serviceAddress->extra, 'service.service_address', $paths);
        self::appendExtraPaths($payload->currentPeriod->extra, 'current_period', $paths);

        foreach ($payload->currentPeriod->consumptionByTier as $i => $tier) {
            self::appendExtraPaths($tier->extra, "current_period.consumption_by_tier[{$i}]", $paths);
        }

        foreach ($payload->currentPeriod->concepts as $i => $concept) {
            self::appendExtraPaths($concept->extra, "current_period.concepts[{$i}]", $paths);
        }

        foreach ($payload->consumptionHistory as $i => $period) {
            self::appendExtraPaths($period->extra, "consumption_history[{$i}]", $paths);
        }

        self::appendExtraPaths($payload->extraction->extra, 'extraction', $paths);

        foreach ($payload->extraction->warnings as $i => $warning) {
            self::appendExtraPaths($warning->extra, "extraction.warnings[{$i}]", $paths);
        }

        foreach ($payload->extraction->evidence as $i => $evidence) {
            self::appendExtraPaths($evidence->extra, "extraction.evidence[{$i}]", $paths);
        }

        return $paths;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @param  list<string>  $paths
     */
    private static function appendExtraPaths(array $extra, string $prefix, array &$paths): void
    {
        foreach (array_keys($extra) as $key) {
            $paths[] = $prefix === '' ? (string) $key : $prefix.'.'.$key;
        }
    }

    /**
     * @param  list<int|float|null>  $values
     */
    private static function sumDecimal(array $values): ?string
    {
        $sum = '0';
        $hasValue = false;

        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $hasValue = true;
            $sum = bcadd($sum, DecimalComparator::toDecimalString($value), 6);
        }

        return $hasValue ? $sum : null;
    }

    private static function parseDate(?string $date): ?CarbonImmutable
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($date);
        } catch (Throwable) {
            return null;
        }
    }
}
