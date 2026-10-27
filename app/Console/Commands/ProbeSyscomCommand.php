<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infrastructure\Syscom\SyscomCatalog;
use App\Infrastructure\Syscom\SyscomUnavailable;
use Illuminate\Console\Command;

final class ProbeSyscomCommand extends Command
{
    protected $signature = 'helios:syscom:probe';

    protected $description = 'Verificar acceso de lectura al catálogo Syscom sin mostrar tokens ni precios.';

    public function handle(SyscomCatalog $catalog): int
    {
        try {
            $result = $catalog->search('panel solar');
        } catch (SyscomUnavailable $failure) {
            $this->error($failure->reason);

            return self::FAILURE;
        }
        $products = $result['productos'] ?? null;
        if (! is_array($products)) {
            $this->error('invalid_product_response');

            return self::FAILURE;
        }
        $this->info('Conexión de lectura correcta: '.count($products).' productos en la primera página. Sin pedidos ni reservas.');

        return self::SUCCESS;
    }
}
