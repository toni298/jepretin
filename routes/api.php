<?php

use App\Http\Controllers\Api\Cron\SyncPhotosController;
use App\Http\Middleware\VerifyCronSecret;
use Illuminate\Support\Facades\Route;

Route::get('/cron/sync-photos', SyncPhotosController::class)
    ->middleware(VerifyCronSecret::class)
    ->name('cron.sync-photos');
