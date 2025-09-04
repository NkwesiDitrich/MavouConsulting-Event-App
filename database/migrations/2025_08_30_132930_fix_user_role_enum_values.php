<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB; // ← Add this import
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('organizer', 'member', 'admin') DEFAULT 'member'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('organizer', 'attendee', 'admin') DEFAULT 'organizer'");
    }
};