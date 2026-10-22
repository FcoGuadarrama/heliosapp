<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\Support;

/**
 * Comparación de cifras (dinero o kWh) con precisión decimal, sin
 * comparaciones de float "ingenuas" (`==`, resta directa de floats).
 *
 * Usa bcmath internamente: los valores numéricos se formatean a cadena con
 * una escala fija antes de cualquier operación aritmética.
 */
final class DecimalComparator
{
    private const SCALE = 6;

    /**
     * Indica si `$a` y `$b` difieren en más de `$toleranceFraction` (por
     * ejemplo 0.01 para 1%), relativo a `$base` (por defecto, `$b`).
     *
     * Devuelve null cuando no hay suficiente información para decidir
     * (alguno de los valores es null): la ausencia de un dato no es una
     * discrepancia, es un dato faltante.
     */
    public static function relativeDifferenceExceeds(
        int|float|string|null $a,
        int|float|string|null $b,
        float $toleranceFraction,
    ): ?bool {
        if ($a === null || $b === null) {
            return null;
        }

        $aStr = self::toDecimalString($a);
        $bStr = self::toDecimalString($b);

        $diff = bcsub($aStr, $bStr, self::SCALE);
        $absDiff = self::abs($diff);

        $base = self::abs($bStr);

        if (bccomp($base, '0', self::SCALE) === 0) {
            // Base cero: cualquier diferencia no nula es una discrepancia.
            return bccomp($absDiff, '0', self::SCALE) !== 0;
        }

        $relativeDiff = bcdiv($absDiff, $base, self::SCALE);

        return bccomp($relativeDiff, (string) $toleranceFraction, self::SCALE) === 1;
    }

    public static function isNegative(int|float|string|null $value): bool
    {
        if ($value === null) {
            return false;
        }

        return bccomp(self::toDecimalString($value), '0', self::SCALE) === -1;
    }

    /**
     * Convierte a una representación decimal en cadena, validando que sea
     * numérica cuando el origen ya era una cadena (dato no confiable).
     *
     * @return numeric-string
     */
    public static function toDecimalString(int|float|string $value): string
    {
        if (is_string($value)) {
            if (! is_numeric($value)) {
                throw new \InvalidArgumentException("El valor \"{$value}\" no es una cifra numérica válida.");
            }

            return $value;
        }

        return sprintf('%.'.self::SCALE.'F', $value);
    }

    /**
     * @param  numeric-string  $decimal
     * @return numeric-string
     */
    private static function abs(string $decimal): string
    {
        return bccomp($decimal, '0', self::SCALE) === -1
            ? bcmul($decimal, '-1', self::SCALE)
            : $decimal;
    }
}
