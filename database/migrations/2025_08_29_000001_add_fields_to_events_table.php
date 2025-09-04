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
            // Add new fields
             $table->string('slogan', 100)->nullable()->after('name');
            $table->text('long_description')->nullable()->after('description');
            $table->string('image_path')->nullable()->after('image_url');
            $table->string('place')->nullable()->after('location');
            $table->unsignedBigInteger('category_id')->nullable()->after('place');
            
            // Add foreign key constraint for category
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'slogan',
                'long_description', 
                'image_path',
                'place',
                'category_id'
            ]);
        });
    }
};

