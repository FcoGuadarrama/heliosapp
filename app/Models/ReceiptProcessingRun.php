<?php

namespace App\Models;

use App\Domain\Processing\ProcessingStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['solar_case_id', 'receipt_intake_id', 'status', 'attempts', 'error_code', 'started_at', 'dispatched_at', 'finished_at'])]
class ReceiptProcessingRun extends Model
{
    protected $casts = ['status' => ProcessingStatus::class, 'attempts' => 'integer', 'started_at' => 'immutable_datetime', 'dispatched_at' => 'immutable_datetime', 'finished_at' => 'immutable_datetime'];

    /** @return BelongsTo<SolarCase, $this> */
    public function solarCase(): BelongsTo
    {
        return $this->belongsTo(SolarCase::class);
    }

    /** @return BelongsTo<ReceiptIntake, $this> */
    public function intake(): BelongsTo
    {
        return $this->belongsTo(ReceiptIntake::class, 'receipt_intake_id');
    }
}
