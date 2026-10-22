<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use App\Domain\ReceiptData\DTO\ReceiptPayload;

/**
 * Orquesta las dos validaciones del contrato §1.2 (estructural y de
 * coherencia) y produce el {@see ReceiptValidationResult} final.
 *
 * Este es el único punto de entrada que acepta JSON crudo no confiable.
 */
final class ReceiptImportValidator
{
    public function __construct(
        private readonly ReceiptSchemaValidator $schemaValidator = new ReceiptSchemaValidator,
        private readonly ReceiptCoherenceValidator $coherenceValidator = new ReceiptCoherenceValidator,
    ) {}

    public function validateJson(string $rawJson): ReceiptValidationResult
    {
        $decoded = $this->schemaValidator->decode($rawJson);

        if ($decoded === null) {
            return ReceiptValidationResult::fromViolations(
                $this->schemaValidator->validateJson($rawJson),
            );
        }

        return $this->validateDecoded($decoded);
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    public function validateDecoded(array $decoded): ReceiptValidationResult
    {
        $structuralViolations = $this->schemaValidator->validateStructure($decoded);
        $payload = ReceiptPayload::fromArray($decoded);
        $coherenceViolations = $this->coherenceValidator->validate($payload);

        return ReceiptValidationResult::fromViolations(
            [...$structuralViolations, ...$coherenceViolations],
            $payload,
        );
    }
}
