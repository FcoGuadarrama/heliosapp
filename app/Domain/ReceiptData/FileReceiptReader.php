<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData;

use App\Domain\Processing\ReceiptSource;

interface FileReceiptReader
{
    public function read(ReceiptSource $source): ReceiptReading;
}
