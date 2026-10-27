<?php

namespace App\Console\Commands;

use App\Domain\Processing\ReceiptSource;
use App\Domain\ReceiptData\FileReceiptReader;
use App\Domain\ReceiptData\ReadingAssessment;
use App\Domain\ReceiptData\ReceiptReading;
use App\Domain\ReceiptData\ReceiptReadingFailure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ProbeReceiptReaderCommand extends Command
{
    protected $signature = 'helios:receipts:probe {file : Archivo local autorizado para prueba}
                            {--expected= : JSON local de campos esperados para medir precisión}';

    protected $description = 'Probar lectura real sin crear prospectos ni guardar datos extraídos.';

    public function handle(FileReceiptReader $reader, ReadingAssessment $assessment): int
    {
        $path = $this->argument('file');
        if (! is_string($path) || ! is_file($path) || ! is_readable($path)) {
            $this->error('Archivo no disponible.');

            return self::FAILURE;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $bytes = file_get_contents($path);
        if ($bytes === false || ! is_string($mime)) {
            $this->error('Archivo no legible.');

            return self::FAILURE;
        }
        $disk = Storage::disk('receipts');
        $temporary = 'probes/'.bin2hex(random_bytes(16));
        $started = microtime(true);
        try {
            $disk->put($temporary, $bytes);
            $reading = $reader->read(new ReceiptSource(0, $temporary, $mime));
            $result = $assessment->validate($reading->payloadJson);
            $this->line('Modelo: '.$reading->model.'; páginas: '.$reading->pageCount);
            $this->line('Extracción: '.$result->payload?->extraction->status.'; validación: '.$result->status->value);
            $this->line('Errores: '.count($result->errors).'; advertencias: '.count($result->warnings));
            $this->line('Duración: '.round(microtime(true) - $started, 1).' s.');

            return ! $this->matchesExpected($reading) || $result->isRejected() ? self::FAILURE : self::SUCCESS;
        } catch (ReceiptReadingFailure $failure) {
            $this->error($failure->reason);

            return self::FAILURE;
        } finally {
            $disk->delete($temporary);
        }
    }

    private function matchesExpected(ReceiptReading $reading): bool
    {
        $path = $this->option('expected');
        if ($path === null) {
            return true;
        }
        if (! is_string($path) || ! is_file($path)) {
            $this->error('Archivo de expectativas no disponible.');

            return false;
        }
        $contents = file_get_contents($path);
        if ($contents === false) {
            $this->error('Archivo de expectativas no legible.');

            return false;
        }
        $expected = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        $payload = json_decode($reading->payloadJson, true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($expected) || array_is_list($expected)) {
            $this->error('Las expectativas deben ser un objeto de rutas y valores.');

            return false;
        }
        $matches = 0;
        foreach ($expected as $field => $value) {
            $actual = data_get($payload, $field);
            if ($actual === $value || ((is_int($actual) || is_float($actual))
                && (is_int($value) || is_float($value)) && $actual == $value)) {
                $matches++;
            } else {
                $this->warn('Campo distinto: '.$field);
            }
        }
        $this->line('Coincidencias: '.$matches.'/'.count($expected));

        return $matches === count($expected);
    }
}
