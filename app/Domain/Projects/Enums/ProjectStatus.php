<?php

namespace App\Domain\Projects\Enums;

enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case DATA_RECEIVED = 'data_received';
    case ANALYZING = 'analyzing';
    case ANALYZED = 'analyzed';
    case QUOTED = 'quoted';
    case PROPOSAL_SENT = 'proposal_sent';
    case WON = 'won';
    case LOST = 'lost';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Borrador',
            self::DATA_RECEIVED => 'Datos recibidos',
            self::ANALYZING => 'Analizando',
            self::ANALYZED => 'Analizado',
            self::QUOTED => 'Presupuestado',
            self::PROPOSAL_SENT => 'Propuesta enviada',
            self::WON => 'Ganado',
            self::LOST => 'Perdido',
            self::ARCHIVED => 'Archivado',
        };
    }
}
