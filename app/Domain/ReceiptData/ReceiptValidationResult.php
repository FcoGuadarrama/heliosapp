<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use App\Domain\ReceiptData\DTO\ReceiptPayload;
use App\Domain\ReceiptData\Enums\ImportStatus;
use App\Domain\ReceiptData\Enums\RuleCode;
use App\Domain\ReceiptData\Enums\RuleLevel;
use App\Domain\ReceiptData\ValueObjects\RuleViolation;

/**
 * Resultado agregado de validar un import de recibo CFE: errores,
 * advertencias, campos críticos faltantes, y el estado derivado (contrato
 * §5).
 *
 * `accepted` nunca se deriva aquí: lo asigna una persona después de revisar
 * un import en `needs_review`.
 */
final readonly class ReceiptValidationResult
{
    /**
     * @param  list<RuleViolation>  $errors
     * @param  list<RuleViolation>  $warnings
     * @param  list<string>  $missingCriticalFields
     */
    public function __construct(
        public ImportStatus $status,
        public array $errors,
        public array $warnings,
        public array $missingCriticalFields,
        public ?ReceiptPayload $payload = null,
    ) {}

    /**
     * @param  list<RuleViolation>  $violations  errores y advertencias mezclados
     */
    public static function fromViolations(array $violations, ?ReceiptPayload $payload = null): self
    {
        $errors = [];
        $warnings = [];
        $missingCriticalFields = [];

        foreach ($violations as $violation) {
            if ($violation->level === RuleLevel::Error) {
                $errors[] = $violation;
            } else {
                $warnings[] = $violation;
            }

            if ($violation->code === RuleCode::MissingCriticalField && $violation->field !== null) {
                $missingCriticalFields[] = $violation->field;
            }
        }

        $status = match (true) {
            $errors !== [] => ImportStatus::Rejected,
            $warnings !== [] || $missingCriticalFields !== [] => ImportStatus::NeedsReview,
            default => ImportStatus::Validated,
        };

        return new self(
            status: $status,
            errors: $errors,
            warnings: $warnings,
            missingCriticalFields: array_values(array_unique($missingCriticalFields)),
            payload: $payload,
        );
    }

    public function isRejected(): bool
    {
        return $this->status === ImportStatus::Rejected;
    }

    public function needsReview(): bool
    {
        return $this->status === ImportStatus::NeedsReview;
    }

    public function isValidated(): bool
    {
        return $this->status === ImportStatus::Validated;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'errors' => array_map(fn (RuleViolation $v): array => $v->toArray(), $this->errors),
            'warnings' => array_map(fn (RuleViolation $v): array => $v->toArray(), $this->warnings),
            'missing_critical_fields' => $this->missingCriticalFields,
            'payload' => $this->payload?->toArray(),
        ];
    }
}
