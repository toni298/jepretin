<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;

class EventQrCodeController extends Controller
{
    public function show(Event $event, QrCodeService $qrCode): Response
    {
        Gate::authorize('view', $event);
        abort_unless($event->status === 'active', 404);

        return response($qrCode->generate(route('guest.events.show', ['publicEvent' => $event->slug])))
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
