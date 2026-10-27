<?php

declare(strict_types=1);

namespace App\Infrastructure\Receipts;

use App\Domain\Processing\ReceiptSource;
use App\Domain\ReceiptData\ReceiptReadingFailure;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

final class ReceiptImages
{
    /** @return list<string> Base64 images in document page order. */
    public function prepare(ReceiptSource $source): array
    {
        $disk = Storage::disk('receipts');
        if (! $disk->exists($source->privatePath)
            || $disk->size($source->privatePath) > config('receipt_reader.max_file_bytes')) {
            throw new ReceiptReadingFailure('source_unavailable_or_too_large');
        }
        $path = $disk->path($source->privatePath);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if ($mime !== $source->mimeType || ! in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new ReceiptReadingFailure('unsupported_document');
        }

        return $mime === 'application/pdf' ? $this->pdf($path) : [$this->image($path)];
    }

    private function image(string $path): string
    {
        $size = getimagesize($path);
        if ($size === false || config('receipt_reader.max_image_pixels') < $size[0] * $size[1]
            || filesize($path) > config('receipt_reader.max_file_bytes')) {
            throw new ReceiptReadingFailure('invalid_or_oversized_image');
        }
        $bytes = file_get_contents($path);
        if ($bytes === false) {
            throw new ReceiptReadingFailure('source_unreadable');
        }

        return base64_encode($bytes);
    }

    /** @return list<string> */
    private function pdf(string $path): array
    {
        $info = $this->run([(string) config('receipt_reader.pdfinfo'), $path]);
        if (! preg_match('/^Pages:\s+(\d+)$/m', $info, $matches)) {
            throw new ReceiptReadingFailure('invalid_pdf');
        }
        $pages = (int) $matches[1];
        if ($pages < 1 || $pages > config('receipt_reader.max_pages')) {
            throw new ReceiptReadingFailure('pdf_page_limit');
        }
        $directory = sys_get_temp_dir().'/helios-receipt-'.bin2hex(random_bytes(16));
        if (! mkdir($directory, 0700)) {
            throw new ReceiptReadingFailure('preparation_unavailable');
        }
        try {
            $images = [];
            $encodedBytes = 0;
            for ($page = 1; $page <= $pages; $page++) {
                $prefix = $directory.'/page-'.$page;
                $this->run([(string) config('receipt_reader.pdftoppm'), '-f', (string) $page, '-l', (string) $page,
                    '-singlefile', '-scale-to', '2400', '-png', $path, $prefix]);
                $image = $this->image($prefix.'.png');
                $encodedBytes += strlen($image);
                if ($encodedBytes > config('receipt_reader.max_encoded_bytes')) {
                    throw new ReceiptReadingFailure('document_image_budget');
                }
                $images[] = $image;
            }

            return $images;
        } finally {
            File::deleteDirectory($directory);
        }
    }

    /** @param list<string> $arguments */
    private function run(array $arguments): string
    {
        try {
            $process = new Process($arguments);
            $process->setTimeout(45);
            $process->setEnv(['LC_ALL' => 'C']);
            $process->run();
        } catch (Throwable) {
            throw new ReceiptReadingFailure('pdf_preparation_unavailable');
        }
        if (! $process->isSuccessful()) {
            throw new ReceiptReadingFailure('pdf_preparation_failed');
        }

        return $process->getOutput();
    }
}
