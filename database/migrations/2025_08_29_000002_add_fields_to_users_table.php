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
        Schema::table('users', function (Blueprint $table) {
            // Add new fields
            $table->string('profile_picture')->nullable()->after('email');
            $table->integer('events_attended')->default(0)->after('role');
        });
        
        // Update the default role to 'organizer'
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['organizer', 'attendee', 'admin'])->default('organizer')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_picture', 'events_attended']);
            $table->enum('role', ['organizer', 'attendee', 'admin'])->default('attendee')->change();
        });
    }
};

