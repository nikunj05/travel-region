<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait HotelBedsTrait
{
    /**
     * Generate HotelBeds API signature
     *
     * @return string
     */
    protected function generateSignature(): string
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');   // store in config/services.php
        $secret = env('HOTEL_BEDS_SECRET');    // store in config/services.php

        $timestamp = time(); // current timestamp in seconds
        $rawString = $apiKey . $secret . $timestamp;

        return hash('sha256', $rawString);
    }

    /**
     * Make HotelBeds API request
     *
     * @param Request $request
     * @return \Illuminate\Http\Client\Response
     */
    protected function checkHotelAvailability($request)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->post("https://api.test.hotelbeds.com/hotel-api/1.0/hotels", [
                'stay' => [
                    'checkIn' => $request->check_in,
                    'checkOut' => $request->check_out
                ],
                'occupancies' => [
                    [
                        'rooms' => $request->rooms,
                        'adults' => $request->adults,
                        'children' => $request->children ?? 0
                    ]
                ],
                'geolocation' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'radius' => 50,
                    'unit' => 'km'
                ],
                'language' => $request->language,
            ]);
    }
}
