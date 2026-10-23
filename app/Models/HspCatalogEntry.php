<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['hsp_catalog_version_id', 'state_code', 'state_name', 'annual_hsp', 'monthly_hsp', 'weights'])]
class HspCatalogEntry extends Model
{
    public $timestamps = false;

    protected $casts = ['annual_hsp' => 'decimal:6', 'monthly_hsp' => 'array', 'weights' => 'array'];
}
