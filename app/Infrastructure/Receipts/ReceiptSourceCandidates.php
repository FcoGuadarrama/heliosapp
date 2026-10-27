<?php

declare(strict_types=1);

namespace App\Infrastructure\Receipts;

final class ReceiptSourceCandidates
{
    public function __construct(private CfeTextFacts $parser) {}

    /** @param array{engine: string, pages: list<array<string, mixed>>, text: string} $source
     * @return array{fields: array<string, mixed>, evidence: list<array<string, mixed>>, history: list<array<string, mixed>>|null, verified: bool}
     */
    public function parse(array $source): array
    {
        $facts = $this->parser->parse($source['text']);
        if ($source['engine'] !== 'apple-vision') {
            return $facts;
        }
        $observed = [];
        foreach ($source['pages'] as $page) {
            foreach ($page['blocks'] as $label) {
                if (! preg_match('/^Energ[ií]a\s*\(kWh\)$/iu', $label['text'])) {
                    continue;
                }
                $value = $this->consumption($page['blocks'], $label);
                if ($value !== null) {
                    $observed[] = ['value' => $value, 'page' => $page['number'], 'label' => $label];
                }
            }
        }
        if (count($observed) === 1) {
            $row = $observed[0];
            $facts['fields']['current_period.consumption_kwh'] = (float) str_replace(',', '', $row['value']['text']);
            $box = $row['value'];
            $facts['evidence'][] = ['field' => 'current_period.consumption_kwh', 'page' => $row['page'],
                'text' => $row['label']['text'].' / Total periodo: '.$box['text'],
                'bbox' => [$box['x'], $box['y'], min(1, $box['x'] + $box['width']), min(1, $box['y'] + $box['height'])]];
        }
        // OCR candidates always require confirmation, including apparently complete tables.
        $facts['verified'] = false;

        return $facts;
    }

    /** @param list<array<string, mixed>> $blocks
     * @param  array<string, mixed>  $label
     * @return array<string, mixed>|null
     */
    private function consumption(array $blocks, array $label): ?array
    {
        $headers = array_values(array_filter($blocks, fn (array $block): bool => preg_match('/^Total(?:\s+periodo)?$/iu', $block['text']) === 1
            && $block['y'] < $label['y'] && $label['y'] - $block['y'] < 0.06));
        if (count($headers) !== 1) {
            return null;
        }
        $header = $headers[0];
        $period = array_filter($blocks, fn (array $block): bool => preg_match('/^periodo$/iu', $block['text']) === 1 && abs($block['x'] - $header['x']) < 0.03
            && $block['y'] > $header['y'] && $block['y'] < $label['y']);
        if (! preg_match('/periodo/iu', $header['text']) && count($period) !== 1) {
            return null;
        }
        $values = array_values(array_filter($blocks, fn (array $block): bool => abs($block['y'] - $label['y']) < 0.004 && abs($block['x'] - $header['x']) < 0.03
            && preg_match('/^(?:\d{1,3}(?:,\d{3})+|\d+)(?:\.\d+)?$/', $block['text']) === 1));

        return count($values) === 1 ? $values[0] : null;
    }
}
