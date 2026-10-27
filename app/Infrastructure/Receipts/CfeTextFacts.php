<?php

declare(strict_types=1);

namespace App\Infrastructure\Receipts;

use App\Domain\ReceiptData\ReceiptReadingFailure;
use DateTimeImmutable;

final class CfeTextFacts
{
    private const MONTHS = ['ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04', 'MAY' => '05', 'JUN' => '06',
        'JUL' => '07', 'AGO' => '08', 'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'];

    /** @return array{fields: array<string, mixed>, evidence: list<array<string, mixed>>, history: list<array<string, mixed>>|null, verified: bool} */
    public function parse(?string $text): array
    {
        $fields = [];
        $evidence = [];
        $history = [];
        $candidates = 0;
        $page = 1;
        $wires = false;
        $periodText = '';
        $conflicts = [];
        foreach (explode("\n", $text ?? '') as $line) {
            if (preg_match('/^Página ([1-6]):$/u', $line, $match)) {
                $page = (int) $match[1];
            }
            $observations = $this->fields($line);
            foreach ($observations as $field => $value) {
                if (array_key_exists($field, $fields) && $fields[$field] !== $value) {
                    $conflicts[$field] = true;
                    unset($fields[$field]);
                }
                if (isset($conflicts[$field])) {
                    continue;
                }
                $fields[$field] = $value;
                $evidence[] = ['field' => $field, 'page' => $page, 'text' => trim($line)];
            }
            if (isset($observations['current_period.period_start'])) {
                $periodText = trim($line);
            }
            $wires = $wires || preg_match('/NO\s+HILOS\s*:/iu', $line) === 1;
            if (preg_match('/^\s*del\s+\d{1,2}\b/iu', $line)) {
                $candidates++;
                $row = $this->history($line);
                if ($row !== null) {
                    $index = count($history);
                    $history[] = $row;
                    foreach (['period_start', 'period_end', 'consumption_kwh'] as $key) {
                        $evidence[] = ['field' => 'consumption_history.'.$index.'.'.$key, 'page' => $page, 'text' => trim($line)];
                    }
                }
            }
        }
        $verified = count(array_intersect(['service.tariff_code', 'current_period.period_start',
            'current_period.period_end', 'current_period.consumption_kwh'], array_keys($fields))) === 4
            && ($wires || array_key_exists('service.phases', $fields));
        if ($verified && $wires && ! array_key_exists('service.phases', $fields)) {
            $fields['service.phases'] = null;
        }
        if ($verified && ! array_key_exists('current_period.days', $fields)) {
            $fields['current_period.days'] = null;
        }
        $completeHistory = $candidates > 0 && $candidates === count($history);

        return ['fields' => $fields, 'evidence' => $evidence,
            'history' => $completeHistory ? $history : null,
            'verified' => $verified && $completeHistory && $periodText !== ''];
    }

    /** @return array<string, mixed> */
    private function fields(string $line): array
    {
        $fields = [];
        if (preg_match('/TARIFA\s*:\s*([A-Z0-9-]+?)(?=NO\.?\s*MEDIDOR|\s|$)/iu', $line, $m)) {
            $fields['service.tariff_code'] = strtoupper($m[1]);
        }
        if (preg_match('/PERIODO FACTURADO\s*:\s*(\d{1,2}\s+[A-Z]{3}\s+\d{2,4})\s*-\s*(\d{1,2}\s+[A-Z]{3}\s+\d{2,4})/iu', $line, $m)) {
            $start = $this->date($m[1]);
            $end = $this->date($m[2]);
            if ($start !== null && $end !== null) {
                $fields['current_period.period_start'] = $start;
                $fields['current_period.period_end'] = $end;
            }
        }
        if (preg_match('/Energ[ií]a\s*\(kWh\)\s+([\d,.-]+)\s+([\d,.-]+)\s+([\d,.-]+)\s*$/iu', $line, $m)) {
            $fields['current_period.consumption_kwh'] = $this->number($m[3]);
        }
        if (preg_match('/\bFASES\s*:\s*([123])\b/iu', $line, $m)) {
            $fields['service.phases'] = (int) $m[1];
        }
        if (preg_match('/^\s*D[IÍ]AS\s*:\s*(\d{1,3})\s*$/iu', $line, $m)) {
            $fields['current_period.days'] = (int) $m[1];
        }

        return $fields;
    }

    /** @return array<string, mixed>|null */
    private function history(string $line): ?array
    {
        if (! preg_match('/^\s*del\s+(\d{1,2}\s+[A-Z]{3}\s+\d{2,4})\s+al\s+(\d{1,2}\s+[A-Z]{3}\s+\d{2,4})\s+([\d,.-]+)\s+\$?([\d,.]+)/iu', $line, $m)) {
            return null;
        }
        $start = $this->date($m[1]);
        $end = $this->date($m[2]);
        if ($start === null || $end === null) {
            return null;
        }

        return ['period_start' => $start, 'period_end' => $end, 'days' => null,
            'consumption_kwh' => $this->number($m[3]), 'amount_mxn' => $this->number($m[4]), 'source' => 'receipt_table'];
    }

    private function date(string $printed): ?string
    {
        $parts = preg_split('/\s+/', strtoupper(trim($printed)));
        if ($parts === false || count($parts) !== 3 || ! isset(self::MONTHS[$parts[1]])) {
            return null;
        }
        if (! in_array(strlen($parts[2]), [2, 4], true)) {
            return null;
        }
        $date = DateTimeImmutable::createFromFormat('!d m '.(strlen($parts[2]) === 2 ? 'y' : 'Y'),
            $parts[0].' '.self::MONTHS[$parts[1]].' '.$parts[2]);
        $errors = DateTimeImmutable::getLastErrors();

        return $date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            ? null : $date->format('Y-m-d');
    }

    private function number(string $printed): float
    {
        if (! preg_match('/^-?(?:\d{1,3}(?:,\d{3})+|\d+)(?:\.\d+)?$/D', $printed)) {
            throw new ReceiptReadingFailure('source_numeric_format');
        }

        return (float) str_replace(',', '', $printed);
    }
}
