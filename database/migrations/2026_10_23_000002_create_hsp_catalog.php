<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hsp_catalog_versions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reference')->unique();
            $table->string('status')->default('updating');
            $table->string('methodology')->nullable();
            $table->string('sampling_sha256', 64)->nullable();
            $table->longText('sampling_manifest')->nullable();
            $table->json('source_metadata')->nullable();
            $table->string('error_code')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('hsp_catalog_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hsp_catalog_version_id')->constrained()->restrictOnDelete();
            $table->char('state_code', 2);
            $table->string('state_name');
            $table->decimal('annual_hsp', 9, 6);
            $table->json('monthly_hsp');
            $table->json('weights');
            $table->unique(['hsp_catalog_version_id', 'state_code']);
        });
        Schema::create('hsp_catalog_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hsp_catalog_version_id')->constrained()->restrictOnDelete();
            $table->string('tile');
            $table->json('query');
            $table->longText('raw_response');
            $table->string('sha256', 64);
            $table->timestamp('retrieved_at');
            $table->unique(['hsp_catalog_version_id', 'tile']);
        });
        Schema::create('hsp_catalog_publications', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->foreignId('hsp_catalog_version_id')->constrained()->restrictOnDelete();
        });
        Schema::create('hsp_refresh_alerts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hsp_catalog_version_id')->unique()->constrained()->restrictOnDelete();
            $table->string('code');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hsp_refresh_alerts');
        Schema::dropIfExists('hsp_catalog_publications');
        Schema::dropIfExists('hsp_catalog_sources');
        Schema::dropIfExists('hsp_catalog_entries');
        Schema::dropIfExists('hsp_catalog_versions');
    }
};
