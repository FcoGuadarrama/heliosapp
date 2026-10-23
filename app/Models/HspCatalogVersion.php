<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['reference', 'status', 'methodology', 'sampling_sha256', 'sampling_manifest', 'source_metadata', 'error_code', 'published_at', 'expires_at'])]
class HspCatalogVersion extends Model
{
    protected $casts = ['source_metadata' => 'array', 'published_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime'];
}
