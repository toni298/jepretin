<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GenerateUniqueEventSlug;
use App\Actions\GenerateUniqueEventUploadToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventIndexRequest;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Repositories\EventRepository;
use App\Services\SupabaseStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(EventIndexRequest $request, EventRepository $events): Response
    {
        $filters = $request->validated();

        return Inertia::render('Event/Index', [
            'events' => $events->paginateForUser($request->user(), $filters),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'summary' => $events->summaryFor($request->user()),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Event::class);

        return Inertia::render('Event/Create');
    }

    public function store(
        StoreEventRequest $request,
        GenerateUniqueEventSlug $generateUniqueEventSlug,
        GenerateUniqueEventUploadToken $generateUniqueEventUploadToken,
        EventRepository $events,
    ): RedirectResponse {
        $data = $request->validated();
        $event = $events->createFor($request->user(), [
            ...$data,
            'slug' => $generateUniqueEventSlug->handle($data['title']),
            'upload_token' => $generateUniqueEventUploadToken->handle(),
        ]);

        return redirect()->route('events.show', $event)
            ->with('success', 'Acara berhasil dibuat.');
    }

    public function show(Event $event, SupabaseStorage $storage, EventRepository $events): Response
    {
        Gate::authorize('view', $event);
        $events->loadPhotoCounts($event);

        return Inertia::render('Event/Show', [
            'event' => $event,
            'guestUrl' => route('guest.events.show', ['publicEvent' => $event->slug]),
            'galleryUrl' => $event->status === 'active'
                ? route('public.events.gallery', ['publicEvent' => $event->slug])
                : null,
            'guestUploadUrl' => $event->upload_token
                ? route('guest.events.upload-token', ['uploadToken' => $event->upload_token])
                : route('guest.events.show', ['publicEvent' => $event->slug]),
            'qrCodeUrl' => $event->status === 'active' ? route('events.qr', $event) : null,
            'coverPhotoUrl' => $event->cover_photo_path
                ? $storage->publicUrl($event->cover_photo_path)
                : null,
        ]);
    }

    public function edit(Event $event): Response
    {
        Gate::authorize('update', $event);

        return Inertia::render('Event/Edit', [
            'event' => $event,
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event, EventRepository $events): RedirectResponse
    {
        $events->update($event, $request->validated());

        return redirect()->route('events.show', $event)
            ->with('success', 'Acara berhasil diperbarui.');
    }

    public function destroy(Event $event, EventRepository $events): RedirectResponse
    {
        Gate::authorize('delete', $event);
        $events->archive($event);

        return redirect()->route('events.index')
            ->with('success', 'Acara berhasil diarsipkan.');
    }
}
