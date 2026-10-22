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
            $table->date('event_date')->nullable();
            $table->text('location')->nullable();
            $table->string('cover_photo_path')->nullable();
            $table->string('gdrive_folder_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'event_date',
                'location',
                'cover_photo_path',
                'gdrive_folder_id',
            ]);
        });
    }
};
