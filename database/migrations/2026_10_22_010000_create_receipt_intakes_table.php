<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_intakes', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->string('sha256', 64);
            $table->string('privacy_version');
            $table->text('privacy_text');
            $table->text('privacy_url')->nullable();
            $table->timestamp('consented_at');
            $table->string('status')->default('stored');
            $table->string('reader_identifier')->nullable();
            $table->string('reader_version')->nullable();
            $table->string('fixture_identifier')->nullable();
            $table->longText('raw_payload')->nullable();
            $table->json('validation_result')->nullable();
            $table->timestamps();
            $table->index(['client_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_intakes');
    }
};
