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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('name');
            $table->text('description');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('location')->nullable();
            $table->string('virtual_link')->nullable();
            $table->integer('capacity')->nullable(); // null means unlimited
            $table->boolean('allow_waitlist')->default(false);
            $table->boolean('is_public')->default(true);
            $table->string('access_code')->nullable(); // for private events
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('published');
            $table->timestamps();

            // Indexes for performance
            $table->index('organizer_id');
            $table->index('start_time');
            $table->index('is_public');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
