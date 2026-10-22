<?php

namespace App\Http\Controllers\Api\Cron;

use App\Http\Controllers\Controller;
use App\Services\GoogleDrivePhotoSync;
use Illuminate\Http\JsonResponse;

class SyncPhotosController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GoogleDrivePhotoSync $sync): JsonResponse
    {
        return response()->json($sync->handle(
            (int) config('services.google_drive.sync_batch_size', 1),
        ));
    }
}
