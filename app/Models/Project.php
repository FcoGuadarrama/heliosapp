<?php

namespace App\Models;

use App\Domain\Projects\Enums\InstallationType;
use App\Domain\Projects\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'code',
        'name',
        'status',
        'installation_type',
        'property_type',
        'street',
        'exterior_number',
        'neighborhood',
        'municipality',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'objectives',
        'property_data',
        'notes',
        'assigned_user_id',
        'created_by',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        'installation_type' => InstallationType::class,
        'objectives' => 'array',
        'property_data' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isMissingCoordinates(): bool
    {
        return is_null($this->latitude) || is_null($this->longitude);
    }
}
