<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\ValueObjects;

use App\Domain\ReceiptData\Enums\RuleCode;
use App\Domain\ReceiptData\Enums\RuleLevel;

/**
 * Una violación concreta de una regla estructural o de coherencia, detectada
 * sobre un payload de recibo CFE.
 */
final readonly class RuleViolation
{
    public function __construct(
        public RuleCode $code,
        public RuleLevel $level,
        public string $message,
        public ?string $field = null,
    ) {}

    public static function make(RuleCode $code, string $message, ?string $field = null, ?RuleLevel $level = null): self
    {
        return new self(
            code: $code,
            level: $level ?? $code->defaultLevel(),
            message: $message,
            field: $field,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code->value,
            'level' => $this->level->value,
            'field' => $this->field,
            'message' => $this->message,
        ];
    }
}
