<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO\Concerns;

/**
 * Permite que un DTO del dominio ReceiptData conserve íntegros los campos
 * desconocidos que llegan en el JSON de origen (principio §1.4 del
 * contrato: "nada se descarta en silencio").
 */
trait HasExtraFields
{
    /**
     * Separa las claves conocidas de un arreglo de las desconocidas.
     *
     * @param  array<string, mixed>  $raw
     * @param  list<string>  $knownKeys
     * @return array<string, mixed>
     */
    protected static function captureExtra(array $raw, array $knownKeys): array
    {
        return array_diff_key($raw, array_flip($knownKeys));
    }

    /**
     * Combina los campos conocidos (ya serializados a su forma original)
     * con los campos desconocidos conservados, en ese orden.
     *
     * @param  array<string, mixed>  $known
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected static function mergeExtra(array $known, array $extra): array
    {
        return array_merge($known, $extra);
    }
}
