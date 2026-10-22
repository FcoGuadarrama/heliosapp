<?php

namespace App\Models;

use App\Domain\Clients\Enums\ClientType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'contact_name',
        'email',
        'phone',
        'rfc',
        'street',
        'exterior_number',
        'interior_number',
        'neighborhood',
        'municipality',
        'state',
        'postal_code',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'type' => ClientType::class,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
