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

        if (!$bookings->count()) {
            return 0;
        }

        foreach ($bookings as $booking) {
            $refunds = Http::withToken(env('TAP_SECRET'))
                ->acceptJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.tap.company/v2/refunds', [
                    'charge_id' => $booking->tap_charge_id,
                    'amount' => $booking->refunded_amount,
                    'currency' => $booking->refunded_currency,
                    'reason' => 'Booking order ' . $booking->order . ' cancelled by user',
                ]);

            if ($refunds->successful()) {

                Booking::where('id', $booking->id)->update([
                    'status' => 'cancelled',
                    'tap_refund_id' => $refunds->json()['id'],
                    'cancellation_in_progress' => false,
                ]);

                return [
                    'status' => true,
                    'message' => 'Booking cancelled successfully',
                    'data' => [],
                ];
            } else {
                Log::error('HotelBeds Booking Cancellation Refund Failed', $refunds->json());
            }
        }

        return 0;
    }
}
