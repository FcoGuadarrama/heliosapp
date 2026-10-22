<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->enum('type', ['residential', 'commercial', 'industrial', 'agricultural'])->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep nullable: restoring NOT NULL would require classifying prospects
        // and must never silently change or delete their data during rollback.
    }
};
