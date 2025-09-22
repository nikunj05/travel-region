<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Interfaces\BookingInterface;
use Illuminate\Http\Request;

class BookingController extends Controller
{
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
}
