<?php

declare(strict_types=1);

use App\Domain\ReceiptData\ReceiptSchemaValidator;
use Tests\TestCase;
use Tests\Unit\ReceiptData\ReceiptFixtures as F;

uses(TestCase::class);

test('un payload completo no produce violaciones estructurales', function () {
    $violations = (new ReceiptSchemaValidator)->validateStructure(F::base());

    expect($violations)->toBe([]);
});

test('detecta la ausencia de cada uno de los 5 campos estructuralmente obligatorios', function (string $dotPath) {
    $payload = F::set(F::base(), $dotPath, null);

    $codes = array_map(fn ($v) => $v->code->value, (new ReceiptSchemaValidator)->validateStructure($payload));

    expect($codes)->toContain('MISSING_REQUIRED_FIELD');
})->with([
    'schema_version',
    'document.source_type',
    'document.extracted_at',
    'document.extractor',
    'extraction.status',
]);

test('casi todos los demás campos son opcionales: su ausencia no produce violaciones estructurales', function () {
    $minimal = [
        'schema_version' => '1.0',
        'document' => [
            'source_type' => 'manual',
            'extracted_at' => '2026-10-20T10:00:00-06:00',
            'extractor' => ['identifier' => 'mock', 'version' => '1.0.0'],
        ],
        'extraction' => ['status' => 'partial'],
    ];

    $violations = (new ReceiptSchemaValidator)->validateStructure($minimal);

    expect($violations)->toBe([]);
});
