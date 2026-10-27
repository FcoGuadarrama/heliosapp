<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_readings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receipt_intake_id')->unique()->constrained()->restrictOnDelete();
            $table->string('model');
            $table->string('prompt_version');
            $table->unsignedSmallInteger('page_count');
            $table->longText('raw_response');
            $table->longText('payload_json');
            $table->json('validation_result');
            $table->string('status');
            $table->timestamps();
        });
        Schema::create('receipt_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('receipt_reading_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('revision');
            $table->text('reason');
            $table->longText('payload_json');
            $table->json('validation_result');
            $table->json('normalization');
            $table->timestamps();
            $table->unique(['receipt_reading_id', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_reviews');
        Schema::dropIfExists('receipt_readings');
    }
};
