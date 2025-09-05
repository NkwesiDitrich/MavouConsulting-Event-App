<?php

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
        Schema::create('event_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->nullable(); // 1-5 star rating
            $table->text('comment')->nullable();
            $table->json('feedback_data')->nullable(); // Additional structured feedback
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            // Unique constraint to prevent duplicate feedback
            $table->unique(['event_id', 'user_id']);
            
            // Indexes for performance
            $table->index('event_id');
            $table->index('user_id');
            $table->index('rating');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_feedback');
    }
};
