<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->as('api.')
    ->group(function (): void {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');

    Route::prefix('password')->group(function (): void {
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');

        Route::group(['prefix' => 'hotels'], function (): void {
            Route::post('/', [HotelController::class, 'index'])->name('hotels.index');
            Route::get('/{hotelCode}/details', [HotelController::class, 'show'])->name('hotels.details');
        });
    });
});
