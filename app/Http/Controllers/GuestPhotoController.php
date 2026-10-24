<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGuestPhotoUploadUrlRequest;
use App\Http\Requests\StoreGuestPhotoRequest;
use App\Models\Event;
use App\Repositories\PhotoRepository;
use App\Services\GuestPhotoQuota;
use App\Services\SupabaseStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class GuestPhotoController extends Controller
{
    public function uploadUrl(
        CreateGuestPhotoUploadUrlRequest $request,
        Event $publicEvent,
        SupabaseStorage $storage,
        GuestPhotoQuota $quota,
    ): JsonResponse {
        $deviceId = $quota->ensureDeviceId($request);
        $deviceHash = $quota->deviceHash($deviceId);
        $quota->assertCanUpload($publicEvent, $deviceHash);

        return response()->json($storage->createGuestPhotoUpload(
            $publicEvent,
            $request->validated('content_type'),
        ));
    }

    public function store(
        StoreGuestPhotoRequest $request,
        Event $publicEvent,
        SupabaseStorage $storage,
        PhotoRepository $photos,
        GuestPhotoQuota $quota,
    ): JsonResponse {
        $data = $request->validated();
        $deviceId = $quota->ensureDeviceId($request);
        $deviceHash = $quota->deviceHash($deviceId);
        $quota->assertCanUpload($publicEvent, $deviceHash);
        $metadata = $storage->verifyGuestPhoto($data['path'], $data['content_type']);
        $photo = DB::transaction(function () use ($publicEvent, $data, $metadata, $photos, $quota, $deviceHash) {
            $event = Event::query()
                ->whereKey($publicEvent->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $quota->assertCanUpload($event, $deviceHash);

            return $photos->createForEvent($event, [
                'storage_path' => $data['path'],
                'original_filename' => $data['filename'],
                'mime_type' => $metadata['mimeType'],
                'file_size' => $metadata['size'],
                'guest_device_hash' => $deviceHash,
            ]);
        });

        return response()->json([
            'photo' => [
                'id' => $photo->id,
                'url' => $storage->publicUrl($photo->storage_path),
            ],
            'message' => 'Foto berhasil ditambahkan ke galeri acara.',
            'uploadedCount' => $quota->uploadedCount($publicEvent, $deviceHash),
        ], 201);
    }
}
