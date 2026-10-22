<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleDrivePhotoSync
{
    private const MAX_BATCH_SIZE = 10;

    public function __construct(private GoogleDriveService $drive) {}

    /**
     * @return array{batch_limit: int, selected: int, synced: int, retrying: int, failed: int, skipped: int}
     */
    public function handle(int $requestedBatchSize): array
    {
        $batchLimit = min(max(1, $requestedBatchSize), self::MAX_BATCH_SIZE);
        $maxAttempts = max(1, (int) config('services.google_drive.sync_max_attempts', 5));
        $staleBefore = now()->subMinutes(max(1, (int) config('services.google_drive.sync_stale_minutes', 30)));

        $photos = Photo::query()
            ->with('event')
            ->whereNotNull('storage_path')
            ->whereHas('event', fn (Builder $query) => $query->whereNotNull('gdrive_folder_id'))
            ->where(function (Builder $query) use ($maxAttempts, $staleBefore): void {
                $query->where(function (Builder $query) use ($maxAttempts): void {
                    $query->where('status', 'pending_sync')
                        ->where('sync_attempts', '<', $maxAttempts);
                })->orWhere(function (Builder $query) use ($staleBefore): void {
                    $query->where('status', 'syncing')
                        ->where('sync_started_at', '<=', $staleBefore);
                });
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit($batchLimit)
            ->get();

        $summary = [
            'batch_limit' => $batchLimit,
            'selected' => $photos->count(),
            'synced' => 0,
            'retrying' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        foreach ($photos as $photo) {
            $newAttemptCount = (int) $photo->sync_attempts + 1;
            $claim = Photo::query()
                ->whereKey($photo->getKey())
                ->where(function (Builder $query) use ($maxAttempts, $staleBefore): void {
                    $query->where(function (Builder $query) use ($maxAttempts): void {
                        $query->where('status', 'pending_sync')
                            ->where('sync_attempts', '<', $maxAttempts);
                    })->orWhere(function (Builder $query) use ($staleBefore): void {
                        $query->where('status', 'syncing')
                            ->where('sync_started_at', '<=', $staleBefore);
                    });
                })
                ->update([
                    'status' => 'syncing',
                    'sync_attempts' => DB::raw('sync_attempts + 1'),
                    'sync_started_at' => now(),
                    'sync_error' => null,
                ]);

            if ($claim !== 1) {
                $summary['skipped']++;

                continue;
            }

            $folderId = $photo->event?->gdrive_folder_id;

            if (! is_string($folderId) || $folderId === '') {
                Photo::query()->whereKey($photo->getKey())->update([
                    'status' => 'pending_sync',
                    'sync_started_at' => null,
                ]);
                $summary['skipped']++;

                continue;
            }

            try {
                $fileId = $this->drive->uploadPhoto($photo, $folderId);
                $updated = Photo::query()
                    ->whereKey($photo->getKey())
                    ->where('status', 'syncing')
                    ->update([
                        'status' => 'synced',
                        'gdrive_file_id' => $fileId,
                        'sync_started_at' => null,
                        'sync_error' => null,
                        'synced_at' => now(),
                    ]);

                $summary[$updated === 1 ? 'synced' : 'skipped']++;
            } catch (Throwable $exception) {
                $isFinalAttempt = $newAttemptCount >= $maxAttempts;
                Photo::query()
                    ->whereKey($photo->getKey())
                    ->where('status', 'syncing')
                    ->update([
                        'status' => $isFinalAttempt ? 'sync_failed' : 'pending_sync',
                        'sync_started_at' => null,
                        'sync_error' => $exception::class,
                    ]);

                Log::warning('Google Drive photo synchronization failed.', [
                    'photo_id' => $photo->getKey(),
                    'exception_class' => $exception::class,
                ]);

                $summary[$isFinalAttempt ? 'failed' : 'retrying']++;
            }
        }

        return $summary;
    }
}
