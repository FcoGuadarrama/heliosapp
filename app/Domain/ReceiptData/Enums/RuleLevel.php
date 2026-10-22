<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\Enums;

/**
 * Nivel de severidad de una regla de validación de coherencia.
 */
enum RuleLevel: string
{
    case Error = 'error';
    case Warning = 'warning';
}
