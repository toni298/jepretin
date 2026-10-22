<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGuestPhotoUploadUrlRequest;
use App\Http\Requests\StoreGuestPhotoRequest;
use App\Models\Event;
use App\Repositories\PhotoRepository;
use App\Services\SupabaseStorage;
use Illuminate\Http\JsonResponse;

class GuestPhotoController extends Controller
{
    public function uploadUrl(
        CreateGuestPhotoUploadUrlRequest $request,
        Event $publicEvent,
        SupabaseStorage $storage,
    ): JsonResponse {
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
    ): JsonResponse {
        $data = $request->validated();
        $metadata = $storage->verifyGuestPhoto($data['path'], $data['content_type']);
        $photo = $photos->createForEvent($publicEvent, [
            'storage_path' => $data['path'],
            'original_filename' => $data['filename'],
            'mime_type' => $metadata['mimeType'],
            'file_size' => $metadata['size'],
        ]);

        return response()->json([
            'photo' => [
                'id' => $photo->id,
                'url' => $storage->publicUrl($photo->storage_path),
            ],
            'message' => 'Foto berhasil ditambahkan ke galeri acara.',
        ], 201);
    }
}
