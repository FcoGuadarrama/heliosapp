<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['receipt_reading_id', 'created_by', 'revision', 'reason', 'payload_json', 'validation_result', 'normalization'])]
#[Hidden(['reason', 'payload_json', 'validation_result', 'normalization'])]
class ReceiptReview extends Model
{
    protected $casts = ['revision' => 'integer', 'validation_result' => 'array', 'normalization' => 'array'];
}
