<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShowGuestEventRequest;
use App\Models\Event;
use App\Services\GuestPhotoQuota;
use App\Services\QrCodeService;
use App\Services\SupabaseStorage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class GuestEventController extends Controller
{
    public function upload(
        Event $publicEvent,
        SupabaseStorage $storage,
        Request $request,
        GuestPhotoQuota $quota,
    ): Response {
        abort_unless($publicEvent->status === 'active', 404);
        $deviceId = $quota->ensureDeviceId($request);

        $coverPhotoUrl = $publicEvent->cover_photo_path
            ? $storage->publicUrl($publicEvent->cover_photo_path)
            : null;

        return Inertia::render('Guest/Upload', [
            'event' => [
                'slug' => $publicEvent->slug,
                'uploadToken' => $publicEvent->upload_token,
                'title' => $publicEvent->title,
                'brideName' => $publicEvent->bride_name,
                'groomName' => $publicEvent->groom_name,
                'eventDate' => $publicEvent->event_date?->toDateString(),
                'location' => $publicEvent->location,
                'coverPhotoUrl' => $coverPhotoUrl,
                'galleryUrl' => route('public.events.gallery', ['publicEvent' => $publicEvent->slug]),
                'guestPhotoCount' => $quota->uploadedCount($publicEvent, $quota->deviceHash($deviceId)),
                'maxGuestPhotos' => GuestPhotoQuota::MAX_PHOTOS,
            ],
            'seo' => $this->eventMetadata(
                $publicEvent,
                route('guest.events.show', ['publicEvent' => $publicEvent->slug], false),
                $coverPhotoUrl,
            ),
        ]);
    }

    public function uploadByToken(
        Event $uploadToken,
        SupabaseStorage $storage,
        Request $request,
        GuestPhotoQuota $quota,
    ): Response {
        return $this->upload($uploadToken, $storage, $request, $quota);
    }

    public function invitation(ShowGuestEventRequest $request, Event $publicEvent, SupabaseStorage $storage): Response
    {
        abort_unless($publicEvent->status === 'active', 404);
        $guestName = trim($request->validated('to', ''));
        $query = $guestName !== '' ? ['to' => $guestName] : [];
        $coverPhotoUrl = $publicEvent->cover_photo_path
            ? $storage->publicUrl($publicEvent->cover_photo_path)
            : null;

        return Inertia::render('Guest/Event', [
            'event' => [
                'title' => $publicEvent->title,
                'brideName' => $publicEvent->bride_name,
                'groomName' => $publicEvent->groom_name,
                'eventDate' => $publicEvent->event_date?->toDateString(),
                'location' => $publicEvent->location,
                'coverPhotoUrl' => $coverPhotoUrl,
            ],
            'seo' => $this->eventMetadata(
                $publicEvent,
                route('guest.events.invitation', ['publicEvent' => $publicEvent->slug], false),
                $coverPhotoUrl,
            ),
            'guestName' => $guestName !== '' ? $guestName : 'Tamu Undangan',
            'qrCodeUrl' => route('guest.events.qr', [
                'publicEvent' => $publicEvent->slug,
                ...$query,
            ]),
        ]);
    }

    /**
     * @return array{title: string, description: string, image: string, url: string}
     */
    private function eventMetadata(Event $event, string $url, ?string $coverPhotoUrl): array
    {
        $coupleNames = trim(implode(' & ', array_filter([
            trim((string) $event->bride_name),
            trim((string) $event->groom_name),
        ])));
        $title = $coupleNames !== ''
            ? "Pernikahan {$coupleNames} | Jepretin"
            : "{$event->title} | Jepretin";
        $description = $coupleNames !== ''
            ? "Bagikan dan lihat foto candid pernikahan {$coupleNames} di Jepretin."
            : "Bagikan dan lihat foto candid dari {$event->title} di Jepretin.";

        return [
            'title' => $title,
            'description' => $description,
            'image' => $this->forceHttps($coverPhotoUrl ?? secure_asset('banner/banner-1.webp')),
            'url' => $this->forceHttps(secure_url($url)),
        ];
    }

    private function forceHttps(string $url): string
    {
        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        if (preg_match('/^https?:\/\//i', $url) === 1) {
            return preg_replace('/^http:/i', 'https:', $url, 1) ?? $url;
        }

        return secure_url($url);
    }

    public function qr(ShowGuestEventRequest $request, Event $publicEvent, QrCodeService $qrCode): HttpResponse
    {
        abort_unless($publicEvent->status === 'active', 404);
        $guestName = trim($request->validated('to', ''));
        $query = $guestName !== '' ? ['to' => $guestName] : [];
        $url = route('guest.events.show', [
            'publicEvent' => $publicEvent->slug,
            ...$query,
        ]);

        return response($qrCode->generate($url))
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
