<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Validación de recibos CFE
    |--------------------------------------------------------------------------
    |
    | Umbrales usados por App\Domain\ReceiptData para validar el esquema y la
    | coherencia del JSON de recibo CFE (docs/cfe-receipt-schema.md).
    |
    | Son decisiones de ingeniería de software, no reglas de CFE: no afectan
    | ningún cálculo energético ni financiero.
    |
    */
    'receipts' => [

        // Versiones del esquema que Helios acepta estructuralmente.
        'supported_schema_versions' => ['1.0'],

        // Tolerancia relativa para comparar sumas e importes (1%).
        'amount_tolerance_percent' => 0.01,

        // Duración válida de un periodo facturado, en días.
        'period_min_days' => 20,
        'period_max_days' => 75,

        // Desviación máxima tolerada entre `days` declarado y el calculado
        // a partir de `period_start`/`period_end`, en días.
        'period_days_mismatch_tolerance_days' => 2,

        // Hueco máximo tolerado entre periodos consecutivos del historial,
        // en días.
        'history_gap_max_days' => 10,

        // Umbral de desviaciones estándar para marcar un periodo del
        // historial como atípico.
        'consumption_outlier_sigma' => 3,

        // Confianza mínima aceptable (0..1) para un campo crítico.
        'low_confidence_threshold' => 0.70,

        // Campos críticos del contrato §3: sin ellos no puede producirse un
        // diagnóstico energético. Su ausencia no es un error de esquema.
        'critical_fields' => [
            'current_period.consumption_kwh',
            'current_period.period_start',
            'current_period.period_end',
            'service.tariff_code',
        ],

        // Catálogo de claves de tarifa CFE conocidas por Helios. No es
        // exhaustivo ni normativo: solo evita marcar como desconocidas las
        // tarifas domésticas y comerciales más comunes. Ver D3 en
        // docs/architecture.md (fuente oficial de tarifas pendiente).
        'known_tariff_codes' => [
            '1', '1A', '1B', '1C', '1D', '1E', '1F',
            'DAC',
            'PDBT', 'GDBT', 'GDMTO', 'GDMTH', 'GDMTH-R',
            'RA', 'APBT', 'APMT',
            'HM', 'HS', 'HSL', 'HT', 'HTL',
            '2', '3', '3A', '5', '5A', '6', '7', '9', '9-CU', '9-N', '9-M',
            'OM', 'O-M', 'I-15', 'I-30', 'I-A3',
        ],
    ],

];
