<?php

use App\Http\Controllers\TapWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::any('/webhook/tap', [TapWebhookController::class, 'handle'])->name('tap.webhook');
