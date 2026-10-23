<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['solar_case_id', 'client_id', 'created_by', 'source', 'file_path', 'mime_type', 'file_size', 'sha256', 'privacy_version', 'privacy_text', 'privacy_url', 'consented_at', 'status', 'reader_identifier', 'reader_version', 'fixture_identifier', 'raw_payload', 'validation_result'])]
#[Hidden(['file_path', 'sha256', 'raw_payload', 'privacy_text'])]
class ReceiptIntake extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['consented_at' => 'immutable_datetime', 'validation_result' => 'array'];
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }
}
