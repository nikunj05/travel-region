<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\PopularDestinationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TapPaymentController;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->as('api.')
    ->group(function (): void {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('social-auth', [AuthController::class, 'socialAuth'])->name('auth.social-auth');

    Route::prefix('password')->group(function (): void {
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');
    });

    Route::group(['prefix' => 'hotels'], function (): void {
        Route::post('/', [HotelController::class, 'index'])->name('hotels.index');
        Route::get('/locations/destinations', [HotelController::class, 'locationsDestinations'])->name('hotels.locations.destinations');
        Route::post('/{hotelCode}/details', [HotelController::class, 'show'])->name('hotels.details');
        Route::get('/accommodation-types', [HotelController::class, 'accommodationTypes'])->name('hotels.accommodation-types');
    });

    Route::get('blog-categories', [BlogController::class, 'categories'])->name('blog.categories');
    Route::get('blog-tags', [BlogController::class, 'tags'])->name('blog.tags');
    Route::resource('blogs', BlogController::class)->only(['index', 'show']);
    Route::resource('testimonials', TestimonialController::class)->only(['index', 'show']);

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');

    Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');

    Route::get('popular-destinations', [PopularDestinationController::class, 'index'])->name('popular-destinations.index');

    Route::get('pages', [CmsController::class, 'index'])->name('cms.index');
    Route::get('pages/{slug}', [CmsController::class, 'show'])->name('cms.show');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');

        Route::post('blogs/{blog}/comment', [BlogController::class, 'storeComment'])->name('blog.comments.store');

        Route::get('bookings', [BookingController::class, 'index'])->name('booking.index');
        Route::get('bookings/{order}', [BookingController::class, 'show'])->name('booking.show');
        Route::get('bookings/{order}/pdf', [BookingController::class, 'downloadPdf'])->name('booking.download-pdf');
        Route::delete('bookings/{order}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
        Route::post('bookings', [BookingController::class, 'store'])->name('booking.store');
        Route::post('apply-coupon', [BookingController::class, 'applyCoupon'])->name('booking.apply-coupon');

        Route::post('checkout', [TapPaymentController::class, 'checkout'])->name('payment.checkout');

        Route::group(['prefix' => 'profile'], function (): void {
            Route::get('/', [AuthController::class, 'profile'])->name('auth.profile');
            Route::put('/', [AuthController::class, 'updateProfile'])->name('auth.update-profile');
        });

        Route::group(['prefix' => 'notification-preferences'], function (): void {
            Route::get('/', [SettingController::class, 'notificationPreferences'])->name('settings.get-notification-preferences');
            Route::put('/', [SettingController::class, 'updateNotificationPreferences'])->name('settings.update-notification-preferences');
        });

        Route::group(['prefix' => 'user-settings'], function (): void {
            Route::get('/', [SettingController::class, 'userSettings'])->name('settings.get-user-settings');
            Route::put('/', [SettingController::class, 'updateUserSettings'])->name('settings.update-user-settings');
        });

        Route::group(['prefix' => 'favorite-hotels'], function (): void {
            Route::get('/', [HotelController::class, 'listFavorites'])->name('hotels.list-favorites');
            Route::post('/', [HotelController::class, 'addFavorite'])->name('hotels.add-favorite');
            Route::delete('/{hotelCode}', [HotelController::class, 'removeFavorite'])->name('hotels.remove-favorite');
        });
    });
});
