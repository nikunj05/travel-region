<?php

namespace App\Repositories;

use App\Http\Resources\BookingResource;
use App\Interfaces\BookingInterface;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingRoom;
use App\Models\BookingRoomCancellationPolicy;
use App\Models\Coupon;
use App\Traits\HotelBedsTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingRepository implements BookingInterface
{
    use HotelBedsTrait;

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
        })->with('details')->paginate();

        return $bookings;
    }

    /**
     * Display the specified booking.
     *
     * @param int $order
     * @return Booking
     */
    public function show($order)
    {
        $booking = Booking::where('order', $order)
            ->where('user_id', Auth::id())
            ->with('details', 'booking_room')
            ->firstOrFail();

        return $booking;
    }

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
            'hotel_name' => $request->hotel_name,
            'hotel_location' => $request->hotel_location,
            'hotel_images' => $request->hotel_images,
            'rooms' => $request->rooms,
            'adults' => $request->adults,
            'children' => $request->children ?? 0,
            'nights' => $request->nights,
            'total_price' => $request->total_price,
            'currency' => $request->currency,
        ]);

        $booking->update([
            'order' => 'TR_' . Carbon::now()->format('Y') . $booking->id,
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
                'room_name' => $room['room_name'] ?? null,
                'board_name' => $room['board_name'] ?? null,
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

    public function downloadPdf($order)
    {
        $booking = Booking::where('order', $order)->firstOrFail();

        // File name & full path
        $fileName = 'booking-confirmation-' . $booking->id . '.pdf';
        $filePath = public_path('booking-pdfs/' . $fileName);

        // Create folder if not exists
        if (!file_exists(public_path('booking-pdfs'))) {
            mkdir(public_path('booking-pdfs'), 0777, true);
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.booking-confirmation', [
            'booking' => $booking
        ])->setPaper('A4', 'portrait');

        // Save to public folder
        $pdf->save($filePath);

        // Public URL
        $publicUrl = url('booking-pdfs/' . $fileName);

        return [
            'status' => true,
            'message' => __('messages.booking.pdf.generated'),
            'data' => [
                'pdf_url' => $publicUrl,
            ],
        ];
    }

    public function showCancellationPolicies($order)
    {
        $booking = Booking::where('order', $order)->firstOrFail();

        $bookingRoomCancellationPolicy = BookingRoomCancellationPolicy::whereIn('booking_room_id', function ($query) use ($booking) {
            $query->select('id')
                ->from('booking_rooms')
                ->where('booking_id', $booking->id);
        })->get();

        return [
            'status' => true,
            'message' => __('messages.booking.cancellation-policies-fetched'),
            'data' => [
                'cancellation_policies' => $bookingRoomCancellationPolicy,
            ],
        ];
    }
}
