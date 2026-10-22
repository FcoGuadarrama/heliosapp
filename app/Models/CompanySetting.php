<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $company_name
 * @property string|null $legal_name
 * @property string|null $rfc
 * @property string|null $logo_path
 * @property array<string, mixed>|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $primary_color
 * @property string|null $secondary_color
 * @property string $timezone
 * @property string $default_currency
 * @property int $proposal_validity_days
 * @property string|null $responsible_name
 * @property string|null $responsible_title
 * @property array<string, mixed>|null $legal_texts
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'company_name',
    'legal_name',
    'rfc',
    'logo_path',
    'address',
    'phone',
    'email',
    'website',
    'primary_color',
    'secondary_color',
    'timezone',
    'default_currency',
    'proposal_validity_days',
    'responsible_name',
    'responsible_title',
    'legal_texts',
    'updated_by',
])]
class CompanySetting extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'address' => 'array',
            'legal_texts' => 'array',
        ];
    }

    /**
     * Get the current company settings (always id = 1).
     */
    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'timezone' => 'America/Mexico_City',
                'default_currency' => 'MXN',
                'proposal_validity_days' => 30,
            ]
        );
    }
}
