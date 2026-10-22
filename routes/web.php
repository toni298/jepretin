<?php

use App\Http\Controllers\Admin\CoverUploadController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventQrCodeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestEventController;
use App\Http\Controllers\GuestPhotoController;
use App\Http\Controllers\ProfileController;
use App\Models\Event;
use App\Models\User;
use App\Repositories\EventRepository;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing/Index', [
        'loginUrl' => route('login'),
    ]);
});

Route::bind('publicEvent', function (string $slug): Event {
    return app(EventRepository::class)->findBySlugOrFail($slug);
});

Route::bind('uploadToken', function (string $token): Event {
    return app(EventRepository::class)->findByUploadTokenOrFail($token);
});

Route::get('/event-upload/{uploadToken}', [GuestEventController::class, 'uploadByToken'])
    ->name('guest.events.upload-token');
Route::get('/e/{publicEvent:slug}', [GuestEventController::class, 'upload'])
    ->name('guest.events.show');
Route::get('/e/{publicEvent:slug}/invitation', [GuestEventController::class, 'invitation'])
    ->name('guest.events.invitation');
Route::get('/e/{publicEvent:slug}/qr.svg', [GuestEventController::class, 'qr'])
    ->name('guest.events.qr');
Route::post('/e/{publicEvent:slug}/upload-url', [GuestPhotoController::class, 'uploadUrl'])
    ->middleware('throttle:10,1')
    ->name('guest.events.upload-url');
Route::post('/e/{publicEvent:slug}/photos', [GuestPhotoController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('guest.events.photos.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
});

Route::bind('event', function (string $value): Event {
    $user = request()->user();

    abort_unless($user instanceof User, 404);

    return app(EventRepository::class)->findOwnedOrFail($user, $value);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:viewAny,'.Event::class)
        ->name('dashboard');

    Route::post('/events/cover-upload-signature', [CoverUploadController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('events.cover-upload-signature');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::get('/events/{event}/qr.svg', [EventQrCodeController::class, 'show'])->name('events.qr');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
});
