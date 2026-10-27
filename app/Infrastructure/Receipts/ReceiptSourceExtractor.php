<?php

declare(strict_types=1);

namespace App\Infrastructure\Receipts;

use App\Domain\ReceiptData\ReceiptReadingFailure;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;
use Throwable;

final class ReceiptSourceExtractor
{
    public function __construct(private ReceiptSourceLayout $layout) {}

    /** @return array{engine: string, pages: list<array<string, mixed>>, text: string} */
    public function extract(string $path, bool $forceOcr = false): array
    {
        $path = realpath($path);
        if ($path === false || ! is_file($path) || filesize($path) > 20 * 1024 * 1024) {
            throw new ReceiptReadingFailure('source_invalid_file');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
            return $this->ocr($path, 1);
        }
        if ($mime !== 'application/pdf') {
            throw new ReceiptReadingFailure('source_invalid_file');
        }
        $info = $this->run([(string) config('receipt_reader.pdfinfo'), $path]);
        if (! preg_match('/^Pages:\s+(\d+)/m', $info, $match) || (int) $match[1] < 1 || (int) $match[1] > 6) {
            throw new ReceiptReadingFailure('source_page_limit');
        }
        $text = $this->run([(string) config('receipt_reader.pdftotext'), '-layout', '-enc', 'UTF-8', $path, '-']);
        if (! $forceOcr && preg_match('/PERIODO FACTURADO/iu', $text) && preg_match('/Energ[ií]a\s*\(kWh\)/iu', $text)) {
            $pages = [];
            $parts = explode("\f", $text);
            for ($index = 0; $index < (int) $match[1]; $index++) {
                $pages[] = ['number' => $index + 1, 'text' => $parts[$index] ?? '', 'blocks' => []];
            }

            return $this->document('pdftotext', $pages);
        }

        return $this->ocr($path, (int) $match[1]);
    }

    /** @return array{engine: string, pages: list<array<string, mixed>>, text: string} */
    private function ocr(string $path, int $pageCount): array
    {
        $binary = (string) config('receipt_reader.ocr_binary');
        if ($binary === '' || ! is_executable($binary)) {
            throw new ReceiptReadingFailure('source_ocr_unavailable');
        }
        try {
            $data = json_decode($this->run([$binary, $path]), true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new ReceiptReadingFailure('source_invalid_ocr');
        }
        if (! is_array($data) || ($data['schemaVersion'] ?? null) !== 'receipt-source/1'
            || ! is_array($data['pages'] ?? null) || count($data['pages']) !== $pageCount) {
            throw new ReceiptReadingFailure('source_invalid_ocr');
        }
        $rules = ['pages' => 'required|array|list', 'pages.*.blocks' => 'present|array|list|max:2000',
            'pages.*.blocks.*.text' => 'required|string|max:2000'];
        foreach (['x', 'y', 'width', 'height', 'confidence'] as $field) {
            $rules['pages.*.blocks.*.'.$field] = 'required|numeric:strict|between:0,1';
        }
        if (Validator::make($data, $rules)->fails()) {
            throw new ReceiptReadingFailure('source_invalid_ocr');
        }
        $pages = [];
        foreach ($data['pages'] as $index => $page) {
            $pages[] = ['number' => $index + 1, 'text' => $this->layout->text($page['blocks']),
                'blocks' => $page['blocks']];
        }

        return $this->document('apple-vision', $pages);
    }

    /** @param list<array<string, mixed>> $pages
     * @return array{engine: string, pages: list<array<string, mixed>>, text: string}
     */
    private function document(string $engine, array $pages): array
    {
        $text = implode("\n", array_map(fn (array $page): string => 'Página '.$page['number'].":\n".$page['text'], $pages));
        if (mb_strlen($text) > 20000) {
            throw new ReceiptReadingFailure('source_text_limit');
        }

        return ['engine' => $engine, 'pages' => $pages, 'text' => $text];
    }

    /** @param list<string> $arguments */
    private function run(array $arguments): string
    {
        try {
            $process = new Process($arguments);
            $process->setTimeout(90);
            $process->run();
        } catch (Throwable) {
            throw new ReceiptReadingFailure('source_extraction_unavailable');
        }
        if (! $process->isSuccessful() || strlen($process->getOutput()) > 2 * 1024 * 1024) {
            throw new ReceiptReadingFailure('source_extraction_failed');
        }

        return $process->getOutput();
    }
}
