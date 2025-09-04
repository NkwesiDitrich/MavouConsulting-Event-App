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
        Schema::table('events', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Rename the column
            $table->renameColumn('user_id', 'organizer_id');
        });
        
        // Add the foreign key constraint back with the new column name
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['organizer_id']);
            
            // Rename the column back
            $table->renameColumn('organizer_id', 'user_id');
        });
        
        // Add the foreign key constraint back with the original column name
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

