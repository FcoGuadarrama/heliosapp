<?php

declare(strict_types=1);

namespace App\Infrastructure\Receipts;

use App\Domain\ReceiptData\ReceiptReadingFailure;
use Illuminate\Support\Arr;

final class ReceiptGrounding
{
    public function __construct(private CfeTextFacts $facts) {}

    /** @param array<string, mixed> $payload
     * @return array{payload: array<string, mixed>, verified: bool}
     */
    public function reconcile(array $payload, ?string $text): array
    {
        foreach (['service', 'current_period', 'extraction'] as $section) {
            if (! is_array($payload[$section] ?? null)) {
                throw new ReceiptReadingFailure('reader_invalid_shape');
            }
        }
        $facts = $this->facts->parse($text);
        $warnings = $payload['extraction']['warnings'] ?? [];
        if (! is_array($warnings)) {
            throw new ReceiptReadingFailure('reader_invalid_shape');
        }
        if (! $facts['verified']) {
            $warnings[] = ['code' => 'SOURCE_UNVERIFIED', 'field' => 'document',
                'message' => 'La fuente requiere comprobación visual del asesor.'];
        } elseif (($payload['extraction']['status'] ?? null) === 'failed') {
            $payload['extraction']['status'] = 'partial';
            $warnings[] = $this->warning('extraction.status');
        }
        foreach ($facts['fields'] as $path => $value) {
            if (! $this->same(Arr::get($payload, $path), $value)) {
                $warnings[] = $this->warning($path);
            }
            Arr::set($payload, $path, $value);
        }
        if ($facts['history'] !== null) {
            if (! $this->same($payload['consumption_history'] ?? null, $facts['history'])) {
                $warnings[] = $this->warning('consumption_history');
            }
            $payload['consumption_history'] = $facts['history'];
        }
        $payload['extraction']['warnings'] = array_values($warnings);
        $evidence = $payload['extraction']['evidence'] ?? [];
        $missing = $payload['extraction']['missing_fields'] ?? [];
        if (! is_array($evidence) || ! is_array($missing)) {
            throw new ReceiptReadingFailure('reader_invalid_shape');
        }
        $payload['extraction']['evidence'] = [...$evidence, ...$facts['evidence']];
        $payload['extraction']['missing_fields'] = array_values(array_filter($missing,
            fn (mixed $field): bool => ! is_string($field) || ! isset($facts['fields'][$field])));

        return ['payload' => $payload, 'verified' => $facts['verified']];
    }

    private function same(mixed $left, mixed $right): bool
    {
        if (is_array($left) && is_array($right) && count($left) === count($right)) {
            foreach ($left as $key => $value) {
                if (! array_key_exists($key, $right) || ! $this->same($value, $right[$key])) {
                    return false;
                }
            }

            return true;
        }

        return $left === $right || ((is_int($left) || is_float($left)) && (is_int($right) || is_float($right)) && $left == $right);
    }

    /** @return array<string, string> */
    private function warning(string $field): array
    {
        return ['code' => 'SOURCE_RECONCILED', 'field' => $field,
            'message' => 'Dato cotejado con el texto del recibo. Conservamos la lectura inicial para revisión.'];
    }
}
