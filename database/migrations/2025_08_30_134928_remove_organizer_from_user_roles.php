<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Change the enum to remove 'organizer'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('member', 'admin') DEFAULT 'member'");
    }

    public function down()
    {
        // Revert back if needed
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('organizer', 'member', 'admin') DEFAULT 'member'");
    }
};