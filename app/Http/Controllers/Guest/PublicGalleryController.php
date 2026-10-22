<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Photo;
use App\Services\SupabaseStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Inertia\Inertia;
use Inertia\Response;

class PublicGalleryController extends Controller
{
    private const PAGE_SIZE = 24;

    public function index(Event $publicEvent, SupabaseStorage $storage): Response
    {
        abort_unless($publicEvent->status === 'active', 404);

        $photos = $this->photoPage($publicEvent, null);

        return Inertia::render('Guest/Gallery', [
            'event' => [
                'slug' => $publicEvent->slug,
                'title' => $publicEvent->title,
                'brideName' => $publicEvent->bride_name,
                'groomName' => $publicEvent->groom_name,
                'eventDate' => $publicEvent->event_date?->toDateString(),
                'location' => $publicEvent->location,
                'coverPhotoUrl' => $publicEvent->cover_photo_path
                    ? $storage->publicUrl($publicEvent->cover_photo_path)
                    : null,
                'uploadUrl' => route('guest.events.show', ['publicEvent' => $publicEvent->slug]),
            ],
            'seo' => [
                'title' => "{$publicEvent->title} — Galeri Foto | Jepretin",
                'description' => "Lihat dan unduh foto-foto dari {$publicEvent->title}.",
                'image' => $publicEvent->cover_photo_path
                    ? $storage->publicUrl($publicEvent->cover_photo_path)
                    : secure_asset('banner/banner-1.webp'),
                'url' => secure_url(route('public.events.gallery', [
                    'publicEvent' => $publicEvent->slug,
                ], false)),
            ],
            'photos' => $this->presentPhotos($photos, $storage),
            'nextCursor' => $photos->nextCursor()?->encode(),
            'hasMore' => $photos->hasMorePages(),
            'photosEndpoint' => route('public.events.gallery.photos', [
                'publicEvent' => $publicEvent->slug,
            ], false),
        ]);
    }

    public function photos(Request $request, Event $publicEvent, SupabaseStorage $storage): JsonResponse
    {
        abort_unless($publicEvent->status === 'active', 404);

        $cursor = $request->query('cursor');

        abort_if($cursor !== null && ! is_string($cursor), 400);

        $photos = $this->photoPage($publicEvent, $cursor);

        return response()->json([
            'data' => $this->presentPhotos($photos, $storage),
            'next_cursor' => $photos->nextCursor()?->encode(),
            'has_more' => $photos->hasMorePages(),
        ]);
    }

    /**
     * @return CursorPaginator<int, Photo>
     */
    private function photoPage(Event $event, ?string $cursor): CursorPaginator
    {
        return $event->photos()
            ->whereNotNull('storage_path')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(
                self::PAGE_SIZE,
                ['id', 'event_id', 'storage_path', 'original_filename', 'mime_type', 'created_at'],
                'cursor',
                Cursor::fromEncoded($cursor),
            );
    }

    /**
     * @return list<array{id: string, url: string, downloadUrl: string, fileName: string, createdAt: string|null}>
     */
    private function presentPhotos(CursorPaginator $photos, SupabaseStorage $storage): array
    {
        return $photos->getCollection()
            ->map(function (Photo $photo) use ($storage): array {
                $path = (string) $photo->storage_path;
                $fileName = basename(str_replace('\\', '/', (string) $photo->original_filename));

                if ($fileName === '' || $fileName === '.') {
                    $fileName = 'photo-'.$photo->getKey();
                }

                $url = $storage->publicUrl($path);
                $downloadUrl = $url.(str_contains($url, '?') ? '&' : '?')
                    .'download='.rawurlencode($fileName);

                return [
                    'id' => (string) $photo->getKey(),
                    'url' => $url,
                    'downloadUrl' => $downloadUrl,
                    'fileName' => $fileName,
                    'createdAt' => $photo->created_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }
}
