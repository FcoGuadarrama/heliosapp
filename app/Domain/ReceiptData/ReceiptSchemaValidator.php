<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use App\Domain\ReceiptData\Enums\RuleCode;
use App\Domain\ReceiptData\ValueObjects\RuleViolation;
use JsonException;

/**
 * Validación ESTRUCTURAL del JSON de recibo CFE (contrato §2).
 *
 * Verifica únicamente lo que el contrato declara estructuralmente
 * obligatorio: que el cuerpo sea JSON válido, que `schema_version` esté
 * soportada, y que los 5 campos requeridos estén presentes
 * (`schema_version`, `document.source_type`, `document.extracted_at`,
 * `document.extractor`, `extraction.status`).
 *
 * Todo lo demás es opcional a este nivel: su ausencia no es un error de
 * esquema (ver {@see ReceiptCoherenceValidator} y el contrato §3).
 */
final class ReceiptSchemaValidator
{
    /**
     * Decodifica el cuerpo recibido. Devuelve null cuando no es JSON válido
     * o cuando el JSON válido no representa un objeto en la raíz (p. ej. un
     * escalar, `null` o un arreglo `[...]`).
     *
     * Un objeto vacío `{}` SÍ es JSON válido y debe decodificar a `[]` para
     * que la validación estructural reporte los campos requeridos
     * ausentes (`MISSING_REQUIRED_FIELD`), en vez de reportar
     * `MALFORMED_JSON`: no son el mismo defecto.
     *
     * La raíz se determina por su primer carácter no blanco, sin decodificar
     * dos veces: solo se intenta `json_decode` cuando ese carácter es `{`.
     *
     * @return array<string, mixed>|null
     */
    public function decode(string $rawJson): ?array
    {
        $trimmed = ltrim($rawJson);

        if ($trimmed === '' || $trimmed[0] !== '{') {
            return null;
        }

        try {
            $decoded = json_decode($rawJson, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * Decodifica y valida de una sola vez. Nunca lanza una excepción no
     * controlada: un cuerpo no decodificable produce `MALFORMED_JSON`.
     *
     * @return list<RuleViolation>
     */
    public function validateJson(string $rawJson): array
    {
        $decoded = $this->decode($rawJson);

        if ($decoded === null) {
            return [
                RuleViolation::make(RuleCode::MalformedJson, 'El cuerpo recibido no es un JSON válido.'),
            ];
        }

        return $this->validateStructure($decoded);
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @return list<RuleViolation>
     */
    public function validateStructure(array $decoded): array
    {
        $violations = [];

        if (! self::hasNonEmptyString($decoded, 'schema_version')) {
            $violations[] = RuleViolation::make(
                RuleCode::MissingRequiredField,
                'Falta el campo requerido "schema_version".',
                'schema_version',
            );
        } else {
            $supported = config('helios.receipts.supported_schema_versions', ['1.0']);

            if (! in_array($decoded['schema_version'], $supported, true)) {
                $violations[] = RuleViolation::make(
                    RuleCode::UnsupportedSchemaVersion,
                    sprintf('La versión de esquema "%s" no está soportada.', $decoded['schema_version']),
                    'schema_version',
                );
            }
        }

        $document = is_array($decoded['document'] ?? null) ? $decoded['document'] : [];

        if (! self::hasNonEmptyString($document, 'source_type')) {
            $violations[] = RuleViolation::make(
                RuleCode::MissingRequiredField,
                'Falta el campo requerido "document.source_type".',
                'document.source_type',
            );
        }

        if (! self::hasNonEmptyString($document, 'extracted_at')) {
            $violations[] = RuleViolation::make(
                RuleCode::MissingRequiredField,
                'Falta el campo requerido "document.extracted_at".',
                'document.extracted_at',
            );
        }

        if (! isset($document['extractor']) || ! is_array($document['extractor']) || $document['extractor'] === []) {
            $violations[] = RuleViolation::make(
                RuleCode::MissingRequiredField,
                'Falta el campo requerido "document.extractor".',
                'document.extractor',
            );
        }

        $extraction = is_array($decoded['extraction'] ?? null) ? $decoded['extraction'] : [];

        if (! self::hasNonEmptyString($extraction, 'status')) {
            $violations[] = RuleViolation::make(
                RuleCode::MissingRequiredField,
                'Falta el campo requerido "extraction.status".',
                'extraction.status',
            );
        }

        return $violations;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function hasNonEmptyString(array $data, string $key): bool
    {
        return isset($data[$key]) && is_string($data[$key]) && $data[$key] !== '';
    }
}
