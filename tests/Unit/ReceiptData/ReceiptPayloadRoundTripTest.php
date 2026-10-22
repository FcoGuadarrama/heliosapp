<?php

declare(strict_types=1);

use App\Domain\ReceiptData\DTO\ReceiptPayload;
use Tests\TestCase;
use Tests\Unit\ReceiptData\ReceiptFixtures as F;

uses(TestCase::class);

test('ningún campo del payload original se pierde al pasar por los DTOs, incluidos campos desconocidos anidados', function () {
    $raw = F::base();

    // Campos desconocidos en varios niveles de anidamiento.
    $raw['loyalty_program_points'] = 42;
    $raw['document']['checksum'] = 'abc123';
    $raw['document']['extractor']['model_hash'] = 'deadbeef';
    $raw['service']['preferred_contact_hour'] = '18:00';
    $raw['service']['service_address']['geo'] = ['lat' => 20.5, 'lng' => -100.3];
    $raw['current_period']['reading_type'] = 'real';
    $raw['current_period']['consumption_by_tier'][0]['co2_factor'] = 0.527;
    $raw['current_period']['concepts'][0]['tax_rate'] = 0.16;
    $raw['consumption_history'][0]['meter_reading'] = 98765;
    $raw['extraction']['model_temperature'] = 0.1;
    $raw['extraction']['evidence'][0]['confidence_raw'] = 'scanner-v3';

    $payload = ReceiptPayload::fromArray($raw);
    $rebuilt = $payload->toArray();

    expect($rebuilt['loyalty_program_points'])->toBe(42);
    expect($rebuilt['document']['checksum'])->toBe('abc123');
    expect($rebuilt['document']['extractor']['model_hash'])->toBe('deadbeef');
    expect($rebuilt['service']['preferred_contact_hour'])->toBe('18:00');
    expect($rebuilt['service']['service_address']['geo'])->toBe(['lat' => 20.5, 'lng' => -100.3]);
    expect($rebuilt['current_period']['reading_type'])->toBe('real');
    expect($rebuilt['current_period']['consumption_by_tier'][0]['co2_factor'])->toBe(0.527);
    expect($rebuilt['current_period']['concepts'][0]['tax_rate'])->toBe(0.16);
    expect($rebuilt['consumption_history'][0]['meter_reading'])->toBe(98765);
    expect($rebuilt['extraction']['model_temperature'])->toBe(0.1);
    expect($rebuilt['extraction']['evidence'][0]['confidence_raw'])->toBe('scanner-v3');

    // Y todos los campos conocidos siguen intactos.
    expect($rebuilt['current_period']['consumption_kwh'])->toBe($raw['current_period']['consumption_kwh']);
    expect($rebuilt['service']['tariff_code'])->toBe($raw['service']['tariff_code']);
});

test('fromArray es tolerante: un payload casi vacío no lanza ninguna excepción', function () {
    $payload = ReceiptPayload::fromArray([]);

    expect($payload->schemaVersion)->toBeNull();
    expect($payload->currentPeriod->consumptionKwh)->toBeNull();
    expect($payload->service->tariffCode)->toBeNull();
    expect($payload->consumptionHistory)->toBe([]);
    expect($payload->currentPeriod->consumptionByTier)->toBe([]);
});

test('fromArray es tolerante ante tipos inesperados en campos anidados', function () {
    $payload = ReceiptPayload::fromArray([
        'schema_version' => '1.0',
        'document' => 'not-an-array',
        'current_period' => ['consumption_by_tier' => 'also-not-an-array'],
        'consumption_history' => 'definitely-not-an-array',
    ]);

    expect($payload->document->sourceType)->toBeNull();
    expect($payload->currentPeriod->consumptionByTier)->toBe([]);
    expect($payload->consumptionHistory)->toBe([]);
});
