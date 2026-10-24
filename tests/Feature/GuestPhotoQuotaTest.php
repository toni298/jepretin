<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Photo;
use App\Services\GuestPhotoQuota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GuestPhotoQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_page_sets_a_device_cookie_when_missing(): void
    {
        $request = new \Illuminate\Http\Request();
        $quota = app(GuestPhotoQuota::class);

        $deviceId = $quota->ensureDeviceId($request);

        $this->assertTrue(Str::isUuid($deviceId));
        $this->assertNotNull(collect(\Illuminate\Support\Facades\Cookie::getQueuedCookies())
            ->first(fn ($cookie) => $cookie->getName() === GuestPhotoQuota::COOKIE_NAME));
    }

    public function test_guest_quota_blocks_upload_after_the_same_device_reaches_the_limit(): void
    {
        $event = Event::factory()->create();
        $deviceId = (string) Str::uuid();
        $deviceHash = hash('sha256', $deviceId);

        Photo::factory()->count(GuestPhotoQuota::MAX_PHOTOS)->create([
            'event_id' => $event->id,
            'guest_device_hash' => $deviceHash,
        ]);

        $this->expectException(ValidationException::class);

        app(GuestPhotoQuota::class)->assertCanUpload($event, $deviceHash);
    }
}
