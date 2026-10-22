<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, EventRepository $events): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        Gate::authorize('viewAny', Event::class);

        return Inertia::render('Dashboard/Index', [
            'userName' => $user->name,
            'events' => $events->dashboardEventsFor($user),
        ]);
    }
}
