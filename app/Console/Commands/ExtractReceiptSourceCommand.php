<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\ReceiptData\ReceiptReadingFailure;
use App\Infrastructure\Receipts\ReceiptSourceCandidates;
use App\Infrastructure\Receipts\ReceiptSourceExtractor;
use Illuminate\Console\Command;

final class ExtractReceiptSourceCommand extends Command
{
    protected $signature = 'helios:receipts:extract {file : PDF local} {--output= : Archivo JSON privado nuevo} {--ocr : Forzar OCR para evaluación}';

    protected $description = 'Extraer texto y campos candidatos sin IA generativa ni cambios en el CRM.';

    public function handle(ReceiptSourceExtractor $extractor, ReceiptSourceCandidates $parser): int
    {
        $output = $this->option('output');
        if (! is_string($output) || $output === '') {
            $this->error('Indica --output con una ruta privada nueva. El JSON contiene datos del recibo.');

            return self::FAILURE;
        }
        $path = $this->argument('file');
        if (! is_string($path)) {
            return self::FAILURE;
        }
        try {
            $source = $extractor->extract($path, (bool) $this->option('ocr'));
        } catch (ReceiptReadingFailure $failure) {
            $this->error($failure->reason);

            return self::FAILURE;
        }
        $facts = $parser->parse($source);
        $json = json_encode(['schema_version' => 'receipt-candidates/1', 'requires_review' => true,
            'source' => $source, 'candidates' => $facts], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $mask = umask(0077);
        try {
            $file = @fopen($output, 'x');
        } finally {
            umask($mask);
        }
        if ($file === false) {
            $this->error('No se pudo crear el archivo. Usa una ruta nueva y un directorio privado existente.');

            return self::FAILURE;
        }
        try {
            $written = fwrite($file, $json);
        } finally {
            fclose($file);
        }
        if ($written !== strlen($json)) {
            $this->error('No se pudo escribir el JSON completo.');

            return self::FAILURE;
        }
        $this->info($source['engine'].': '.count($source['pages']).' páginas; '.count($facts['fields']).' campos candidatos; revisión obligatoria.');

        return self::SUCCESS;
    }
}
