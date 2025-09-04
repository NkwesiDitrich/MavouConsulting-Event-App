<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if the table exists before trying to drop it
        if (Schema::hasTable('event_attendees')) {
            Schema::drop('event_attendees');
        }
    }

    public function down(): void
    {
        // We won't recreate it since we want to use 'attendees' table
    }
};