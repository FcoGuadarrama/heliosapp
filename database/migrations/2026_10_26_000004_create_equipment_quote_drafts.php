<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_quote_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solar_case_id')->constrained()->restrictOnDelete();
            $table->foreignId('solar_calculation_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->uuid('submission_id')->unique();
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->timestamps();
            $table->unique(['solar_case_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_quote_drafts');
    }
};
