<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_calculations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('solar_case_id')->constrained()->restrictOnDelete();
            $table->foreignId('receipt_reading_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('review_revision');
            $table->json('inputs');
            $table->json('result');
            $table->timestamps();
            $table->unique(['receipt_reading_id', 'review_revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_calculations');
    }
};
