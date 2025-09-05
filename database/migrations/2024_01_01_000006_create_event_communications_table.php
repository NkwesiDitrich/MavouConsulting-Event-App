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
        Schema::create('event_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('type')->default('announcement'); // announcement, reminder, update
            $table->string('subject');
            $table->text('message');
            $table->json('recipient_filters')->nullable(); // Filter criteria for recipients
            $table->integer('recipients_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->boolean('is_draft')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index('event_id');
            $table->index('sender_id');
            $table->index('type');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_communications');
    }
};
