<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['solar_case_id', 'receipt_reading_id', 'review_revision', 'inputs', 'result'])]
#[Hidden(['inputs', 'result'])]
class SolarCalculation extends Model
{
    protected $casts = ['inputs' => 'array', 'result' => 'array', 'review_revision' => 'integer'];
}
