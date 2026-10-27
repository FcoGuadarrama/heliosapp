<?php

declare(strict_types=1);

namespace App\Infrastructure\Receipts;

final class ReceiptSourceLayout
{
    /** @param list<array{text: string, confidence: float, x: float, y: float, width: float, height: float}> $blocks */
    public function text(array $blocks): string
    {
        usort($blocks, fn (array $a, array $b): int => $a['y'] <=> $b['y']);
        $rows = [];
        foreach ($blocks as $block) {
            $last = count($rows) - 1;
            if ($last < 0 || abs($block['y'] - $rows[$last][0]['y']) > 0.004) {
                $rows[] = [$block];
            } else {
                $rows[$last][] = $block;
            }
        }
        $lines = [];
        foreach ($rows as $row) {
            usort($row, fn (array $a, array $b): int => $a['x'] <=> $b['x']);
            $lines[] = implode('   ', array_column($row, 'text'));
        }

        return implode("\n", $lines);
    }
}
