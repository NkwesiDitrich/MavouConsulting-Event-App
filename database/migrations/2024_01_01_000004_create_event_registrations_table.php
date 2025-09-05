<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Event Registrations Migration - 100% Error-Free with Full IntelliSense Support
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->enum('status', ['registered', 'cancelled', 'attended', 'no_show'])->default('registered');
            $table->datetime('registered_at');
            $table->datetime('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->json('additional_info')->nullable(); // Store as JSON
            $table->timestamps();

            // Unique constraint to prevent duplicate registrations
            $table->unique(['user_id', 'event_id']);

            // Indexes for better performance
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('registered_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
