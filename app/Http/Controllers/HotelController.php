<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchHotelRequest;
use App\Traits\HotelBedsTrait;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    use HotelBedsTrait;

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
}
