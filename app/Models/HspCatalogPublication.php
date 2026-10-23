<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['id', 'hsp_catalog_version_id'])]
class HspCatalogPublication extends Model
{
    public $incrementing = false;

    public $timestamps = false;
}
