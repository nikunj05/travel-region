<?php

use App\Console\Commands\CancelPendingBookings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:cancel-pending-bookings')
    ->everyTwoMinutes()
    ->withoutOverlapping();
