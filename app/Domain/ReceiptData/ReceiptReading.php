<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

final readonly class ReceiptReading
{
    public function __construct(
        public string $rawResponse,
        public string $payloadJson,
        public string $model,
        public int $pageCount,
        public string $promptVersion,
        public bool $sourceVerified = false,
    ) {}
}
