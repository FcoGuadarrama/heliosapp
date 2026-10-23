<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['solar_case_id', 'created_by', 'event_key', 'event', 'reason_code', 'created_at'])]
class SolarCaseEvent extends Model
{
    public $timestamps = false;
}
