<?php

namespace App\Repositories;

use App\Interfaces\BookingInterface;
use App\Models\Booking;
use App\Models\BookingDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingRepository implements BookingInterface
{
    /**
     * Store a newly created booking in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($request)
    {
        $booking = Booking::updateOrCreate([
            'user_id' => Auth::id(),
            'hotel_code' => $request->hotel_code,
            'status' => 'pending',
        ], [
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'rooms' => $request->rooms,
            'adults' => $request->adults,
            'children' => $request->children ?? 0,
            'total_price' => $request->total_price,
            'currency' => $request->currency,
        ]);

        $booking->details()->delete();
        foreach ($request->details as $detail) {
            BookingDetail::create([
                'booking_id' => $booking->id,
                'room_code' => $detail['room_code'],
                'nights' => $detail['nights'],
                'price_per_night' => $detail['price_per_night'],
                'first_name' => $detail['first_name'],
                'last_name' => $detail['last_name'],
                'email' => $detail['email'],
                'country' => $detail['country'],
                'country_code' => $detail['country_code'],
                'phone' => $detail['phone'],
            ]);
        }

        return $booking;
    }
}
