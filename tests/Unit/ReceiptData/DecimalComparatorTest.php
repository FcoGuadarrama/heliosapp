<?php

declare(strict_types=1);

use App\Domain\ReceiptData\Support\DecimalComparator;
use Tests\TestCase;

uses(TestCase::class);

test('relativeDifferenceExceeds devuelve null cuando falta alguno de los valores', function () {
    expect(DecimalComparator::relativeDifferenceExceeds(null, 100.0, 0.01))->toBeNull();
    expect(DecimalComparator::relativeDifferenceExceeds(100.0, null, 0.01))->toBeNull();
});

test('relativeDifferenceExceeds respeta la tolerancia relativa configurada', function () {
    // 1% de 1000 = 10. Una diferencia de 5 no excede la tolerancia.
    expect(DecimalComparator::relativeDifferenceExceeds(1005.0, 1000.0, 0.01))->toBeFalse();

    // Una diferencia de 15 sí la excede.
    expect(DecimalComparator::relativeDifferenceExceeds(1015.0, 1000.0, 0.01))->toBeTrue();
});

test('relativeDifferenceExceeds nunca usa igualdad de float ingenua: 0.1 + 0.2 no se marca como discrepancia frente a 0.3', function () {
    expect(DecimalComparator::relativeDifferenceExceeds(0.1 + 0.2, 0.3, 0.0001))->toBeFalse();
});

test('isNegative detecta importes negativos con precisión decimal', function () {
    expect(DecimalComparator::isNegative(-0.01))->toBeTrue();
    expect(DecimalComparator::isNegative(0.0))->toBeFalse();
    expect(DecimalComparator::isNegative(null))->toBeFalse();
});
