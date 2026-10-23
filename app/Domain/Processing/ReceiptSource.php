<?php

declare(strict_types=1);

namespace App\Domain\Processing;

final readonly class ReceiptSource
{
    public function __construct(
        public int $intakeId,
        public string $privatePath,
        public string $mimeType,
    ) {}
}
