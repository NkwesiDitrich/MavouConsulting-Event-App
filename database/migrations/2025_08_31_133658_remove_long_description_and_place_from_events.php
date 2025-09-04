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
            // Remove only the columns that actually exist
            if (Schema::hasColumn('events', 'long_description')) {
                $table->dropColumn('long_description');
            }
            
            if (Schema::hasColumn('events', 'place')) {
                $table->dropColumn('place');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Add the columns back
            if (!Schema::hasColumn('events', 'long_description')) {
                $table->text('long_description')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('events', 'place')) {
                $table->string('place')->nullable()->after('location');
            }
        });
    }
};