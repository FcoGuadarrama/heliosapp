<?php

namespace App\Domain\Projects\Enums;

enum InstallationType: string
{
    case ROOF = 'roof';
    case GROUND = 'ground';
    case CARPORT = 'carport';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ROOF => 'Techo',
            self::GROUND => 'Terreno',
            self::CARPORT => 'Estacionamiento',
            self::OTHER => 'Otro',
        };
    }
}
