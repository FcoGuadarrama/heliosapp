<?php

declare(strict_types=1);

namespace App\Infrastructure\Receipts;

use App\Domain\Processing\ReceiptSource;
use App\Domain\ReceiptData\ReceiptReadingFailure;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

final class ReceiptText
{
    public function extract(ReceiptSource $source): ?string
    {
        if ($source->mimeType !== 'application/pdf') {
            return null;
        }
        try {
            $process = new Process([(string) config('receipt_reader.pdftotext'), '-layout', '-enc', 'UTF-8',
                Storage::disk('receipts')->path($source->privatePath), '-']);
            $process->setTimeout(45);
            $process->run();
        } catch (Throwable) {
            throw new ReceiptReadingFailure('pdf_text_unavailable');
        }
        if (! $process->isSuccessful()) {
            throw new ReceiptReadingFailure('pdf_text_failed');
        }
        $text = $process->getOutput();
        if (mb_strlen($text) > 20000) {
            throw new ReceiptReadingFailure('pdf_text_limit');
        }
        if (! preg_match('/(?:PERIODO FACTURADO|Periodo del|Tarifa:)/iu', $text)
            || ! preg_match('/(?:kWh|consumo)/iu', $text)) {
            return null;
        }
        $pages = [];
        foreach (explode("\f", $text) as $index => $page) {
            if (trim($page) !== '') {
                $pages[] = 'Página '.($index + 1).":\n".$page;
            }
        }

        return implode("\n", $pages);
    }
}
