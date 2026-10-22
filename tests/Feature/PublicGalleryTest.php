<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicGalleryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_active_event_gallery_is_available_to_guests_and_uses_public_storage_urls(): void
    {
        $this->configurePublicStorageUrl();
        $event = Event::factory()->create();
        $photo = Photo::factory()->for($event)->create([
            'storage_path' => "events/{$event->id}/photos/moment.jpg",
            'original_filename' => 'moment.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Photo::factory()->for($event)->create();

        $this->get(route('public.events.gallery', ['publicEvent' => $event->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/Gallery')
                ->where('event.slug', $event->slug)
                ->has('photos', 1)
                ->where('photos.0.id', $photo->id)
                ->where('photos.0.url', "https://storage.test/storage/v1/object/public/gallery/events/{$event->id}/photos/moment.jpg")
                ->where('hasMore', false));
    }

    public function test_gallery_photos_are_cursor_paginated_and_scoped_to_the_event(): void
    {
        $this->configurePublicStorageUrl();
        $event = Event::factory()->create();
        $otherEvent = Event::factory()->create();

        for ($index = 1; $index <= 25; $index++) {
            Photo::factory()->for($event)->create([
                'storage_path' => "events/{$event->id}/photos/{$index}.jpg",
                'original_filename' => "{$index}.jpg",
                'mime_type' => 'image/jpeg',
            ]);
        }

        $otherEventPhoto = Photo::factory()->for($otherEvent)->create([
            'storage_path' => "events/{$otherEvent->id}/photos/private.jpg",
            'original_filename' => 'private.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $url = route('public.events.gallery.photos', ['publicEvent' => $event->slug]);
        $firstPage = $this->getJson($url)
            ->assertOk()
            ->assertJsonCount(24, 'data')
            ->assertJsonPath('has_more', true)
            ->assertJsonMissing(['id' => $otherEventPhoto->id]);

        $cursor = $firstPage->json('next_cursor');
        $this->assertIsString($cursor);
        $this->assertStringContainsString('download=', $firstPage->json('data.0.downloadUrl'));

        $this->getJson($url.'?cursor='.rawurlencode($cursor))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('has_more', false)
            ->assertJsonMissing(['id' => $otherEventPhoto->id]);
    }

    public function test_archived_event_gallery_returns_not_found(): void
    {
        $event = Event::factory()->create(['status' => 'archived']);

        $this->get(route('public.events.gallery', ['publicEvent' => $event->slug]))
            ->assertNotFound();
    }

    public function test_archived_event_photo_endpoint_returns_not_found(): void
    {
        $event = Event::factory()->create(['status' => 'archived']);

        $this->getJson(route('public.events.gallery.photos', ['publicEvent' => $event->slug]))
            ->assertNotFound();
    }

    private function configurePublicStorageUrl(): void
    {
        config([
            'filesystems.disks.s3.bucket' => 'gallery',
            'filesystems.disks.s3.public_url' => 'https://storage.test/storage/v1/object/public/gallery',
        ]);
    }
}
