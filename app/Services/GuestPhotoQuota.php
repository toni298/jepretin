<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuestPhotoQuota
{
    public const COOKIE_NAME = 'jepretin_guest_device';

    public const MAX_PHOTOS = 5;

    public function deviceId(Request $request): ?string
    {
        $deviceId = $request->cookie(self::COOKIE_NAME);

        return is_string($deviceId) && Str::isUuid($deviceId) ? $deviceId : null;
    }

    public function ensureDeviceId(Request $request): string
    {
        $deviceId = $this->deviceId($request);

        if ($deviceId !== null) {
            return $deviceId;
        }

        $deviceId = (string) Str::uuid();

        Cookie::queue(cookie(
            self::COOKIE_NAME,
            $deviceId,
            60 * 24 * 365,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax',
        ));

        return $deviceId;
    }

    public function deviceHash(?string $deviceId): string
    {
        if (! is_string($deviceId) || ! Str::isUuid($deviceId)) {
            throw ValidationException::withMessages([
                'photo' => 'Sesi perangkat tidak valid. Muat ulang halaman sebelum mengunggah foto.',
            ]);
        }

        return hash('sha256', $deviceId);
    }

    public function uploadedCount(Event $event, string $deviceHash): int
    {
        return $event->photos()
            ->where('guest_device_hash', $deviceHash)
            ->count();
    }

    public function assertCanUpload(Event $event, string $deviceHash): void
    {
        if ($this->uploadedCount($event, $deviceHash) >= self::MAX_PHOTOS) {
            throw ValidationException::withMessages([
                'photo' => 'Batas maksimal 5 foto untuk perangkat ini pada acara tersebut sudah tercapai.',
            ]);
        }
    }
}
