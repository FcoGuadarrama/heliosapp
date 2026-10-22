<?php

namespace App\Domain\Clients\Enums;

enum ClientType: string
{
    case RESIDENTIAL = 'residential';
    case COMMERCIAL = 'commercial';
    case INDUSTRIAL = 'industrial';
    case AGRICULTURAL = 'agricultural';

    public function label(): string
    {
        return match ($this) {
            self::RESIDENTIAL => 'Residencial',
            self::COMMERCIAL => 'Comercial',
            self::INDUSTRIAL => 'Industrial',
            self::AGRICULTURAL => 'Agrícola',
        };
    }
}
