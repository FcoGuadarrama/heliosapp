<?php

namespace Tests\Unit\ReceiptData;

use App\Infrastructure\Receipts\CfeTextFacts;
use App\Infrastructure\Receipts\ReceiptSourceCandidates;
use App\Infrastructure\Receipts\ReceiptSourceLayout;
use PHPUnit\Framework\TestCase;

class ReceiptSourceCandidatesTest extends TestCase
{
    private function block(string $text, float $x, float $y): array
    {
        return ['text' => $text, 'confidence' => 0.99, 'x' => $x, 'y' => $y, 'width' => 0.08, 'height' => 0.01];
    }

    private function blocks(): array
    {
        return [$this->block('Total', 0.54, 0.44), $this->block('periodo', 0.54, 0.456),
            $this->block('Energía (kWh)', 0.05, 0.475), $this->block('45,10ął', 0.19, 0.475),
            $this->block('43,835', 0.35, 0.475), $this->block('1,274', 0.54, 0.475)];
    }

    private function parse(array $blocks): array
    {
        return (new ReceiptSourceCandidates(new CfeTextFacts))->parse(['engine' => 'apple-vision',
            'text' => (new ReceiptSourceLayout)->text($blocks), 'pages' => [['number' => 1, 'blocks' => $blocks]]]);
    }

    public function test_ocr_selects_total_period_column_even_when_meter_reading_is_corrupt(): void
    {
        $result = $this->parse($this->blocks());
        $this->assertSame(1274.0, $result['fields']['current_period.consumption_kwh']);
        $this->assertFalse($result['verified']);
        $this->assertSame(1, $result['evidence'][0]['page']);
        $this->assertSame([0.54, 0.475, 0.62, 0.485], $result['evidence'][0]['bbox']);
    }

    public function test_missing_period_header_does_not_guess_from_last_number(): void
    {
        $blocks = $this->blocks();
        unset($blocks[1]);
        $this->assertArrayNotHasKey('current_period.consumption_kwh', $this->parse(array_values($blocks))['fields']);
    }

    public function test_ambiguous_total_cells_do_not_choose_a_candidate(): void
    {
        $blocks = [...$this->blocks(), $this->block('1,275', 0.55, 0.475)];
        $this->assertArrayNotHasKey('current_period.consumption_kwh', $this->parse($blocks)['fields']);
    }

    public function test_layout_keeps_cells_on_same_line_and_sorts_columns(): void
    {
        $blocks = [$this->block('667', 0.3, 0.2), $this->block('periodo anterior', 0.03, 0.2001),
            $this->block('siguiente fila', 0.03, 0.214)];
        $this->assertSame("periodo anterior   667\nsiguiente fila", (new ReceiptSourceLayout)->text($blocks));
    }
}
