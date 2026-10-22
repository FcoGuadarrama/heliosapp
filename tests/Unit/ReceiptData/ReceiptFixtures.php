<?php

declare(strict_types=1);

namespace Tests\Unit\ReceiptData;

/**
 * Fábrica de payloads de recibo CFE v1.0 para las pruebas del dominio
 * ReceiptData. `base()` devuelve un payload internamente coherente (no
 * dispara ninguna de las 18 reglas); cada prueba lo modifica con `set()`
 * para disparar (o evitar disparar) una regla específica.
 */
final class ReceiptFixtures
{
    /**
     * @return array<string, mixed>
     */
    public static function base(): array
    {
        return [
            'schema_version' => '1.0',
            'document' => [
                'source_type' => 'manual',
                'source_reference' => null,
                'page_count' => 1,
                'extracted_at' => '2026-10-20T10:00:00-06:00',
                'extractor' => [
                    'identifier' => 'mock',
                    'version' => '1.0.0',
                ],
            ],
            'receipt' => [
                'receipt_id' => '0000123456789',
                'issue_date' => '2026-09-15',
                'due_date' => '2026-09-30',
                'total_amount_mxn' => 1234.56,
            ],
            'service' => [
                'service_number' => '123456789012',
                'tariff_code' => '1C',
                'tariff_description' => 'Doméstica 1C',
                'voltage_level' => 'baja_tension',
                'phases' => 1,
                'contracted_demand_kw' => null,
                'billing_cycle' => 'bimonthly',
                'holder_name' => 'Cliente de Prueba',
                'service_address' => [
                    'street' => 'Calle Falsa 123',
                    'neighborhood' => 'Centro',
                    'municipality' => 'Querétaro',
                    'state' => 'Querétaro',
                    'postal_code' => '76000',
                ],
            ],
            'current_period' => [
                'period_start' => '2026-07-15',
                'period_end' => '2026-09-13',
                'days' => 60,
                'consumption_kwh' => 520,
                'consumption_by_tier' => [
                    ['tier' => 'basico', 'kwh' => 520, 'unit_price_mxn' => 2.5, 'amount_mxn' => 1300.00],
                ],
                'demand_kw' => null,
                'amount_mxn' => 1234.56,
                'concepts' => [
                    ['code' => 'energia', 'description' => 'Energía', 'amount_mxn' => 900.00],
                    ['code' => 'transmision', 'description' => 'Transmisión', 'amount_mxn' => 334.56],
                ],
            ],
            'consumption_history' => [
                [
                    'period_start' => '2026-03-15', 'period_end' => '2026-05-14',
                    'days' => 60, 'consumption_kwh' => 450, 'amount_mxn' => 1100.00,
                    'source' => 'receipt_chart',
                ],
                [
                    'period_start' => '2026-05-15', 'period_end' => '2026-07-14',
                    'days' => 60, 'consumption_kwh' => 480, 'amount_mxn' => 1150.00,
                    'source' => 'receipt_chart',
                ],
            ],
            'extraction' => [
                'status' => 'complete',
                'confidence' => [
                    'current_period.consumption_kwh' => 0.97,
                    'current_period.period_start' => 0.95,
                    'current_period.period_end' => 0.95,
                    'service.tariff_code' => 0.99,
                ],
                'missing_fields' => [],
                'warnings' => [],
                'evidence' => [
                    [
                        'field' => 'current_period.consumption_kwh', 'page' => 1,
                        'bbox' => [0.12, 0.44, 0.31, 0.49], 'text' => '520 kWh',
                    ],
                ],
            ],
        ];
    }

    /**
     * Devuelve `base()` con un único valor sobreescrito en una ruta con
     * notación de puntos (p. ej. `current_period.consumption_kwh`).
     *
     * @return array<string, mixed>
     */
    public static function with(string $dotPath, mixed $value): array
    {
        return self::set(self::base(), $dotPath, $value);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function set(array $payload, string $dotPath, mixed $value): array
    {
        $keys = explode('.', $dotPath);
        $cursor = &$payload;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $cursor[$key] = $value;
            } else {
                if (! isset($cursor[$key]) || ! is_array($cursor[$key])) {
                    $cursor[$key] = [];
                }
                $cursor = &$cursor[$key];
            }
        }

        return $payload;
    }
}
