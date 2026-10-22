<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCoverUploadUrlRequest;
use App\Services\SupabaseStorage;
use Illuminate\Http\JsonResponse;

class CoverUploadController extends Controller
{
    public function store(
        CreateCoverUploadUrlRequest $request,
        SupabaseStorage $storage,
    ): JsonResponse {
        return response()->json($storage->createCoverUpload(
            $request->user(),
            $request->validated('content_type'),
        ));
    }
}
