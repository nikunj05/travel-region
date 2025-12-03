<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Traits\HotelBedsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TapWebhookController extends Controller
{
    use HotelBedsTrait;

    public function handle(Request $request)
    {
        $order = $request->reference ? $request->reference['order'] : null;
        $status = $request->status;

        if ($order && $status == 'CAPTURED') {
            $booking = Booking::where('order', $order)->first();

            $bookingDetail = BookingDetail::where('booking_id', $booking->id)->where('is_primary', 1)->first();

            $booking->update([
                'status' => 'confirmed',
                'tap_charge_id' => $request->id,
                'tap_response' => json_encode($request->all()),
            ]);

            $room_rates = [];
            foreach ($booking->booking_room->pluck('rate_key')->toArray() as $rate_key) {
                $room_rates[] = [
                    'rateKey' => $rate_key,
                ];
            }

            $this->bookingConfirmation([
                'booking_id' => $booking->id,
                'first_name' => $bookingDetail->first_name,
                'last_name' => $bookingDetail->last_name,
                'rate_keys' => $room_rates,
                'order' => $booking->order,
                'remark' => $booking->special_requests,
            ]);

            return response()->json(['status' => 'success'], 200);
        }

        Log::warning('Tap Webhook: Invalid data received', $request->all());

        return response()->json(['status' => 'error'], 400);
    }
}
