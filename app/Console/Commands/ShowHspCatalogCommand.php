<?php

namespace App\Console\Commands;

use App\Models\HspCatalogEntry;
use App\Models\HspCatalogPublication;
use App\Models\HspCatalogVersion;
use App\Models\HspRefreshAlert;
use Illuminate\Console\Command;

class ShowHspCatalogCommand extends Command
{
    protected $signature = 'helios:hsp:status {state? : Clave INEGI de dos dígitos}';

    protected $description = 'Consultar HSP local, procedencia, caducidad y último aviso de actualización.';

    public function handle(): int
    {
        $latest = HspCatalogVersion::latest('id')->first();
        if ($latest !== null) {
            $this->line('Última actualización: '.$latest->status.' '.$latest->reference);
        }
        $alert = HspRefreshAlert::latest('id')->first();
        if ($alert !== null) {
            $this->warn('Último aviso interno: '.$alert->code.' (actualización '.$alert->hsp_catalog_version_id.').');
        }
        $publication = HspCatalogPublication::find(1);
        $version = $publication === null ? null : HspCatalogVersion::find($publication->hsp_catalog_version_id);
        if ($version === null || $version->published_at === null || $version->expires_at === null) {
            $this->error('No hay catálogo publicado.');

            return self::FAILURE;
        }
        $this->line('Versión: '.$version->reference.'; metodología: '.$version->methodology);
        $this->line('Fuente: NASA POWER; período: '.json_encode($version->source_metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->line('Publicado: '.$version->published_at->toIso8601String().'; vence: '.$version->expires_at->toIso8601String());
        $state = $this->argument('state');
        $entries = HspCatalogEntry::where('hsp_catalog_version_id', $version->id)
            ->when($state !== null, fn ($query) => $query->where('state_code', $state))->orderBy('state_code')->get();
        if ($entries->isEmpty()) {
            $this->error('Entidad no disponible. Usa clave INEGI 01–32.');

            return self::FAILURE;
        }
        $this->table(['Clave', 'Entidad', 'HSP anual (h/día)'], $entries->map(fn (HspCatalogEntry $entry): array => [
            $entry->state_code, $entry->state_name, $entry->annual_hsp,
        ])->all());
        if ($version->expires_at->lessThanOrEqualTo(now())) {
            $this->error('Catálogo vencido: se conserva como evidencia, pero no habilita cálculos nuevos.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
