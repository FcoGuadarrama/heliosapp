<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['hsp_catalog_version_id', 'tile', 'query', 'raw_response', 'sha256', 'retrieved_at'])]
class HspCatalogSource extends Model
{
    public $timestamps = false;

    protected $casts = ['query' => 'array', 'retrieved_at' => 'immutable_datetime'];
}
