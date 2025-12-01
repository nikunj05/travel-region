<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Http\Requests\CouponCodeRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaginationResource;
use App\Interfaces\BookingInterface;
use App\Models\Booking;
use App\Traits\HotelBedsTrait;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use HotelBedsTrait;

    protected $bookingRepository;

    public function __construct(BookingInterface $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
    * Display a listing of the bookings for the authenticated user.
    *
    * @param Request $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function index(Request $request)
    {
        $bookings = $this->bookingRepository->index($request);

        return $this->sendApiResponse(true, __('messages.booking.listed'), [
            'bookings' => BookingResource::collection($bookings),
            'pagination' => new PaginationResource($bookings)
        ]);
    }

    /**
     * Display the specified booking.
     *
     * @param int $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($order)
    {
        $booking = $this->bookingRepository->show($order);

        return $this->sendApiResponse(true, __('messages.booking.show'), [
            'booking' => new BookingResource($booking),
        ]);
    }

    /**
     * Store a newly created booking in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(BookingRequest $request)
    {
        $booking = $this->bookingRepository->store($request);

        return $this->sendApiResponse(true, __('messages.booking.added'), [
            'booking' => new BookingResource($booking),
        ], 201);
    }

    /**
     * Apply a coupon to the booking.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyCoupon(CouponCodeRequest $request)
    {
        $response = $this->bookingRepository->applyCoupon($request);

        return $this->sendApiResponse($response['status'], $response['message'], $response['data'] ?? []);
    }

    public function downloadPdf($order)
    {
        $response = $this->bookingRepository->downloadPdf($order);

        return $this->sendApiResponse($response['status'], $response['message'], $response['data'] ?? []);
    }

    public function cancel($order)
    {
        $booking = Booking::where('order', $order)->firstOrFail();

        if ($booking->status == 'confirmed') {
            $this->cancelBooking($booking);
        } else {
            $booking->update([
                'status' => 'cancelled',
            ]);
        }

        return $this->sendApiResponse(true, __('messages.booking.cancelled'), [
            'booking' => new BookingResource($booking->fresh()),
        ]);
    }
}
