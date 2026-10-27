<?php

namespace App\Console\Commands;

use App\Application\ReadReceiptFile;
use App\Domain\ReceiptData\ReceiptReadingFailure;
use App\Models\ReceiptIntake;
use Illuminate\Console\Command;

class ReadReceiptCommand extends Command
{
    protected $signature = 'helios:receipts:read {intake : ID interno del recibo privado}';

    protected $description = 'Leer un recibo privado con Ollama y guardar extracción para revisión.';

    public function handle(ReadReceiptFile $read): int
    {
        $intake = ReceiptIntake::whereKey($this->argument('intake'))->firstOrFail();
        try {
            $reading = $read($intake);
        } catch (ReceiptReadingFailure $failure) {
            $this->error($failure->reason);

            return self::FAILURE;
        }
        $this->info('Lectura '.$reading->id.': '.$reading->status.'; '.$reading->page_count.' página(s).');

        return self::SUCCESS;
    }
}
