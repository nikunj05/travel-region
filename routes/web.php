<?php

use App\Http\Controllers\TapWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook/tap', [TapWebhookController::class, 'handle'])->name('tap.webhook');
