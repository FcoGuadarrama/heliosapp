<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * Una advertencia ya declarada por el lector en `extraction.warnings[]`.
 *
 * No debe confundirse con las violaciones (RuleViolation) que produce el
 * propio validador de Helios: esta es información que el lector
 * (Mock/Ollama) ya adjuntó al payload.
 */
final readonly class WarningData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $code = null,
        public ?string $field = null,
        public ?string $message = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['code', 'field', 'message'];

        return new self(
            code: isset($raw['code']) ? (string) $raw['code'] : null,
            field: isset($raw['field']) ? (string) $raw['field'] : null,
            message: isset($raw['message']) ? (string) $raw['message'] : null,
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'code' => $this->code,
            'field' => $this->field,
            'message' => $this->message,
        ], $this->extra);
    }
}
