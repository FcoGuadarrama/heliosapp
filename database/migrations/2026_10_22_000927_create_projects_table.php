<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)
                ->constrained('clients')
                ->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('status', ['draft', 'data_received', 'analyzing', 'analyzed', 'quoted', 'proposal_sent', 'won', 'lost', 'archived']);
            $table->enum('installation_type', ['roof', 'ground', 'carport', 'other']);
            $table->string('property_type')->nullable();
            $table->string('street')->nullable();
            $table->string('exterior_number')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('municipality')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('objectives')->nullable();
            $table->json('property_data')->nullable();
            $table->text('notes')->nullable();
            $table->foreignIdFor(User::class, 'assigned_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignIdFor(User::class, 'created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
