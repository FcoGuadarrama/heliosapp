<?php

namespace App\Models;

use App\Domain\Processing\ProcessingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['public_id', 'client_id', 'responsible_user_id', 'processing_status', 'location_status', 'calculation_status', 'publication_status', 'delivery_status', 'blocker_code', 'attention_required_at', 'simulated'])]
class SolarCase extends Model
{
    protected $casts = ['processing_status' => ProcessingStatus::class, 'attention_required_at' => 'immutable_datetime', 'simulated' => 'boolean'];

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    /** @return HasMany<ReceiptIntake, $this> */
    public function intakes(): HasMany
    {
        return $this->hasMany(ReceiptIntake::class);
    }

    /** @return HasMany<SolarCaseEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(SolarCaseEvent::class);
    }
}
