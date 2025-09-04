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
            $table->integer('max_attendees')->nullable()->after('category_id');
            $table->decimal('ticket_price', 8, 2)->default(0)->after('max_attendees');
            $table->boolean('is_free')->default(true)->after('ticket_price');
            $table->timestamp('registration_deadline')->nullable()->after('is_free');
            $table->json('custom_questions')->nullable()->after('registration_deadline');
            $table->boolean('allow_waitlist')->default(false)->after('custom_questions');
            $table->string('meeting_link')->nullable()->after('allow_waitlist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'max_attendees', 
                'ticket_price', 
                'is_free', 
                'registration_deadline',
                'custom_questions',
                'allow_waitlist',
                'meeting_link'
            ]);
        });
    }
};

