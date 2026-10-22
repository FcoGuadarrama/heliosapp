<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\Enums;

/**
 * Códigos de regla de validación del JSON de recibo CFE.
 *
 * Las primeras 18 constantes corresponden exactamente a la tabla de la
 * sección 4 de docs/cfe-receipt-schema.md (validación de coherencia).
 *
 * `MissingRequiredField` no pertenece a esa tabla: es un código adicional,
 * necesario para que el validador estructural (ReceiptSchemaValidator)
 * pueda reportar la ausencia de alguno de los 5 campos estructuralmente
 * obligatorios (schema_version, document.source_type, document.extracted_at,
 * document.extractor, extraction.status), que no tienen código propio en el
 * contrato. Ver nota de ambigüedad resuelta en el reporte de la tarea F1.6.
 */
enum RuleCode: string
{
    // --- Tabla §4 (18 códigos) ---
    case MalformedJson = 'MALFORMED_JSON';
    case UnsupportedSchemaVersion = 'UNSUPPORTED_SCHEMA_VERSION';
    case NegativeConsumption = 'NEGATIVE_CONSUMPTION';
    case NegativeAmount = 'NEGATIVE_AMOUNT';
    case InvalidPeriod = 'INVALID_PERIOD';
    case FuturePeriod = 'FUTURE_PERIOD';
    case ImpossiblePeriodLength = 'IMPOSSIBLE_PERIOD_LENGTH';
    case PeriodDaysMismatch = 'PERIOD_DAYS_MISMATCH';
    case TierSumMismatch = 'TIER_SUM_MISMATCH';
    case ConceptSumMismatch = 'CONCEPT_SUM_MISMATCH';
    case TotalMismatch = 'TOTAL_MISMATCH';
    case OverlappingHistory = 'OVERLAPPING_HISTORY';
    case HistoryGap = 'HISTORY_GAP';
    case ConsumptionOutlier = 'CONSUMPTION_OUTLIER';
    case UnknownTariff = 'UNKNOWN_TARIFF';
    case MissingCriticalField = 'MISSING_CRITICAL_FIELD';
    case UnknownField = 'UNKNOWN_FIELD';
    case LowConfidence = 'LOW_CONFIDENCE';

    // --- Código estructural adicional, fuera de la tabla §4 ---
    case MissingRequiredField = 'MISSING_REQUIRED_FIELD';

    public function defaultLevel(): RuleLevel
    {
        return match ($this) {
            self::MalformedJson,
            self::UnsupportedSchemaVersion,
            self::NegativeConsumption,
            self::NegativeAmount,
            self::InvalidPeriod,
            self::FuturePeriod,
            self::ImpossiblePeriodLength,
            self::MissingRequiredField => RuleLevel::Error,

            self::PeriodDaysMismatch,
            self::TierSumMismatch,
            self::ConceptSumMismatch,
            self::TotalMismatch,
            self::OverlappingHistory,
            self::HistoryGap,
            self::ConsumptionOutlier,
            self::UnknownTariff,
            self::MissingCriticalField,
            self::UnknownField,
            self::LowConfidence => RuleLevel::Warning,
        };
    }
}
