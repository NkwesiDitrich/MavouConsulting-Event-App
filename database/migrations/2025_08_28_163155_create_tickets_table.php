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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "General Admission", "VIP Pass"
            $table->text('description')->nullable(); // Description of the ticket
            $table->decimal('price', 8, 2); // Price with 2 decimal places
            $table->integer('quantity'); // Total available tickets
            $table->integer('quantity_sold')->default(0); // How many sold
            $table->datetime('sales_start')->nullable(); // When ticket sales start
            $table->datetime('sales_end')->nullable(); // When ticket sales end
            $table->timestamps();
            
            // Optional: Add index for better performance
            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};