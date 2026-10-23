<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['hsp_catalog_version_id', 'code', 'created_at'])]
class HspRefreshAlert extends Model
{
    public $timestamps = false;
}
