<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['receipt_intake_id', 'model', 'prompt_version', 'page_count', 'raw_response', 'payload_json', 'validation_result', 'normalization', 'status'])]
#[Hidden(['raw_response', 'payload_json', 'validation_result', 'normalization'])]
class ReceiptReading extends Model
{
    protected $casts = ['validation_result' => 'array', 'normalization' => 'array', 'page_count' => 'integer'];
}
