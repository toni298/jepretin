<?php

use App\Models\Photo;
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
        Schema::table('photos', function (Blueprint $table) {
            $table->string('status')->default('pending_sync')->change();
            $table->string('gdrive_file_id')->nullable()->unique();
            $table->unsignedSmallInteger('sync_attempts')->default(0);
            $table->text('sync_error')->nullable();
            $table->timestamp('sync_started_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->index(['status', 'created_at'], 'photos_sync_status_created_at_index');
        });

        Photo::query()
            ->where('status', 'pending')
            ->update(['status' => 'pending_sync']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Photo::query()
            ->whereIn('status', ['pending_sync', 'syncing', 'sync_failed'])
            ->update(['status' => 'pending']);

        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex('photos_sync_status_created_at_index');
            $table->dropUnique(['gdrive_file_id']);
            $table->dropColumn([
                'gdrive_file_id',
                'sync_attempts',
                'sync_error',
                'sync_started_at',
                'synced_at',
            ]);
            $table->string('status')->default('pending')->change();
        });
    }
};
