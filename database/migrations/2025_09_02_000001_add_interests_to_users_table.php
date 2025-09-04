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
            $table->json('interests')->nullable()->after('events_attended');
            $table->text('bio')->nullable()->after('interests');
            $table->string('linkedin_url')->nullable()->after('bio');
            $table->string('twitter_url')->nullable()->after('linkedin_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['interests', 'bio', 'linkedin_url', 'twitter_url']);
        });
    }
};

