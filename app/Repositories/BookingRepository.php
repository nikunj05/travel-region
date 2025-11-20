<?php

namespace App\Repositories;

use App\Http\Resources\BookingResource;
use App\Interfaces\BookingInterface;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingRoom;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingRepository implements BookingInterface
{
    /**
     * Display a listing of the bookings for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($request)
    {
        $user = $request->user();
        $bookings = $user->bookings()->when(function ($query) {
            // Add any filtering logic here if needed
            if ($status = request('status')) {
                $query->where('status', $status);
            }
            if ($hotelCode = request('hotel_code')) {
                $query->where('hotel_code', $hotelCode);
            }
        })->with('details')->get();

        return $bookings;
    }

    /**
     * Store a newly created booking in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($request)
    {
        $orderId = uniqid();

        $booking = Booking::updateOrCreate([
            'user_id' => Auth::id(),
            'hotel_code' => $request->hotel_code,
            'status' => 'pending',
        ], [
            'order' => 'ord_' . $orderId,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'rooms' => $request->rooms,
            'adults' => $request->adults,
            'children' => $request->children ?? 0,
            'nights' => $request->nights,
            'total_price' => $request->total_price,
            'currency' => $request->currency,
        ]);

        $booking->details()->delete();
        foreach ($request->details as $detail) {
            BookingDetail::create([
                'booking_id' => $booking->id,
                'price_per_night' => $detail['price_per_night'],
                'first_name' => $detail['first_name'],
                'last_name' => $detail['last_name'],
                'email' => $detail['email'],
                'country' => $detail['country'],
                'country_code' => $detail['country_code'],
                'phone' => $detail['phone'],
                'is_primary' => $detail['is_primary'] ? 1 : 0,
            ]);
        }

        $booking->booking_room()->delete();
        foreach ($request->room_details as $room) {
            BookingRoom::create([
                'booking_id' => $booking->id,
                'room_code' => $room['room_code'],
                'rate_key' => $room['rate_key'],
            ]);
        }

        return $booking;
    }

    /**
     * Apply a coupon to the booking.
     *
     * @param Request $request
     * @return array
     */
    public function applyCoupon($request)
    {
        $coupon = Coupon::where('code', $request->coupon_code)
            ->first();

        if (!$coupon) {
            return [
                'status' => false,
                'message' => __('messages.coupon.invalid'),
            ];
        }

        $booking = Booking::where('order', $request->order)
            ->firstOrFail();

        $discount_amount = 0;
        if ($coupon->type === 'percentage') {
            $discount_amount = ($coupon->discount / 100) * $booking->total_price;
        } elseif ($coupon->type === 'fixed') {
            $discount_amount = $coupon->discount;
        }

        $booking->update([
            'coupon_id' => $coupon->id,
            'discount_amount' => $discount_amount,
        ]);

        return [
            'status' => true,
            'message' => __('messages.coupon.valid'),
            'data' => [
                'booking' => new BookingResource($booking->fresh()),
            ],
        ];
    }
}
