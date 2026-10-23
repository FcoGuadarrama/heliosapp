<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_cases', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('processing_status')->default('received');
            $table->boolean('simulated')->default(false);
            $table->string('location_status')->default('pending');
            $table->string('calculation_status')->default('pending');
            $table->string('publication_status')->default('draft');
            $table->string('delivery_status')->default('not_requested');
            $table->string('blocker_code')->nullable();
            $table->timestamp('attention_required_at')->nullable();
            $table->timestamps();
            $table->index(['responsible_user_id', 'processing_status']);
        });

        Schema::table('receipt_intakes', function (Blueprint $table): void {
            $table->foreignId('solar_case_id')->nullable()->constrained()->restrictOnDelete();
        });

        Schema::create('receipt_processing_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('solar_case_id')->constrained()->restrictOnDelete();
            $table->foreignId('receipt_intake_id')->unique()->constrained()->restrictOnDelete();
            $table->string('status')->default('queued');
            $table->unsignedInteger('attempts')->default(0);
            $table->string('error_code')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('solar_case_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('solar_case_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_key')->unique();
            $table->string('event');
            $table->string('reason_code')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_case_events');
        Schema::dropIfExists('receipt_processing_runs');
        Schema::table('receipt_intakes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('solar_case_id');
        });
        Schema::dropIfExists('solar_cases');
    }
};
