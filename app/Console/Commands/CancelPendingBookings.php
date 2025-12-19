<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CancelPendingBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cancel-pending-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookings = Booking::where('cancellation_in_progress', true)
            ->get();

        $apiKey = env('HOTEL_BEDS_API_KEY');

        $baseUrl = 'https://api.test.hotelbeds.com';
        $version = '1.0';

        foreach ($bookings as $booking) {
            $hotels = Http::withHeaders([
                'Accept' => 'application/json',
                'Api-key' => $apiKey,
                'X-Signature' => $this->generateSignature(),
            ])->delete("{$baseUrl}/hotel-api/{$version}/bookings/{$booking->booking_reference}");

            if ($hotels->successful()) {
                $booking->cancellation_in_progress = false;
                $booking->save();
            } else {
                Log::error('Failed to cancel pending booking', [
                    'booking_reference' => $booking->booking_reference,
                    'response' => $hotels->json(),
                ]);
            }
        }

        return 0;
    }
}
