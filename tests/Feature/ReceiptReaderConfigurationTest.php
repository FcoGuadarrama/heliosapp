<?php

namespace Tests\Feature;

use App\Domain\ReceiptData\FileReceiptReader;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;
use LogicException;
use Tests\TestCase;

class ReceiptReaderConfigurationTest extends TestCase
{
    public function test_removed_source_driver_cannot_be_reactivated(): void
    {
        Http::preventStrayRequests();
        config(['receipt_reader.driver' => 'source']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid receipt reader driver.');
        app(FileReceiptReader::class);
    }

    public function test_local_ocr_export_command_is_not_registered(): void
    {
        $commands = app(Kernel::class)->all();

        $this->assertArrayNotHasKey('helios:receipts:extract', $commands);
        $this->assertArrayHasKey('helios:receipts:probe', $commands);
    }
}
