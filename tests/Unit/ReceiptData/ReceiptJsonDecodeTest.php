<?php

declare(strict_types=1);

use App\Domain\ReceiptData\ReceiptImportValidator;
use Tests\TestCase;

uses(TestCase::class);

/**
 * `MALFORMED_JSON` significa "el cuerpo no es JSON válido" (contrato §4),
 * no "el objeto decodificado está vacío o incompleto". Un objeto JSON vacío
 * `{}` es sintácticamente válido: debe decodificar y dejar que la
 * validación estructural reporte los 5 campos obligatorios ausentes
 * (`MISSING_REQUIRED_FIELD`). En cambio, un arreglo `[]`, un escalar o un
 * cuerpo vacío no son objetos válidos en la raíz: siguen siendo
 * `MALFORMED_JSON`.
 */
test('un objeto JSON vacío "{}" es JSON válido: no es MALFORMED_JSON', function () {
    $result = (new ReceiptImportValidator)->validateJson('{}');

    $errorCodes = array_map(fn ($e) => $e->code->value, $result->errors);

    expect($errorCodes)->not->toContain('MALFORMED_JSON');
    expect($errorCodes)->toContain('MISSING_REQUIRED_FIELD');
    expect($result->status->value)->toBe('rejected');
});

test('un arreglo JSON "[]" en la raíz es MALFORMED_JSON', function () {
    $result = (new ReceiptImportValidator)->validateJson('[]');

    $errorCodes = array_map(fn ($e) => $e->code->value, $result->errors);

    expect($errorCodes)->toContain('MALFORMED_JSON');
    expect($result->status->value)->toBe('rejected');
});

test('un escalar JSON válido en la raíz es MALFORMED_JSON', function (string $body) {
    $result = (new ReceiptImportValidator)->validateJson($body);

    $errorCodes = array_map(fn ($e) => $e->code->value, $result->errors);

    expect($errorCodes)->toContain('MALFORMED_JSON');
    expect($result->status->value)->toBe('rejected');
})->with([
    '"123"',
    'null',
    'true',
    '42',
]);

test('un cuerpo completamente vacío es MALFORMED_JSON', function () {
    $result = (new ReceiptImportValidator)->validateJson('');

    $errorCodes = array_map(fn ($e) => $e->code->value, $result->errors);

    expect($errorCodes)->toContain('MALFORMED_JSON');
    expect($result->status->value)->toBe('rejected');
});

test('un objeto JSON sintácticamente roto sigue siendo MALFORMED_JSON', function () {
    $result = (new ReceiptImportValidator)->validateJson('{ "schema_version": "1.0", ');

    $errorCodes = array_map(fn ($e) => $e->code->value, $result->errors);

    expect($errorCodes)->toContain('MALFORMED_JSON');
    expect($result->status->value)->toBe('rejected');
});
