<?php

namespace App\Console\Commands;

use App\Application\RefreshHspCatalog;
use Illuminate\Console\Command;
use Throwable;

class RefreshHspCatalogCommand extends Command
{
    protected $signature = 'helios:hsp:refresh';

    protected $description = 'Publicar un catálogo HSP completo de 32 entidades con evidencia NASA.';

    public function handle(RefreshHspCatalog $refresh): int
    {
        try {
            $version = $refresh();
        } catch (Throwable $exception) {
            report($exception);
            $this->error('No se publicó el catálogo. Consulta helios:hsp:status y los registros internos.');

            return self::FAILURE;
        }
        $this->info('Catálogo publicado: '.$version->reference.' (32 entidades).');

        return self::SUCCESS;
    }
}
