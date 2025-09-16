<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchHotelRequest;
use App\Traits\HotelBedsTrait;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    use HotelBedsTrait;

    /**
     * Handle the incoming request to search for hotels.
     *
     * @param SearchHotelRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(SearchHotelRequest $request)
    {
        try {
            $response = $this->checkHotelAvailability($request);

            if ($response->successful()) {
                return $this->sendApiResponse(true, __('messages.hotel.fetched'), [
                    'hotels' => $response->json()['hotels']['hotels'],
                    'checkIn' => $response->json()['hotels']['checkIn'],
                    'total' => $response->json()['hotels']['total'],
                    'checkOut' => $response->json()['hotels']['checkOut']
                ]);
            }

            return $this->sendApiResponse(false, __('messages.catch'), [
                'error' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return $this->sendApiResponse(false, __('messages.catch'), [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get details of a specific hotel by its code.
     *
     * @param string $hotelCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $hotelCode)
    {
        try {
            $response = $this->getHotelDetails($request, $hotelCode);

            if ($response->successful()) {
                return $this->sendApiResponse(true, __('messages.hotel.single_fetched'), [
                    'hotel' => $response->json()['hotel']
                ]);
            }

            return $this->sendApiResponse(false, __('messages.catch'), [
                'error' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            return $this->sendApiResponse(false, __('messages.catch'), [
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
