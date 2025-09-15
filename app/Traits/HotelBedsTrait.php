<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait HotelBedsTrait
{
    protected $baseUrl = 'https://api.test.hotelbeds.com';
    protected $version = '1.0';

    /**
     * Generate HotelBeds API signature
     *
     * @return string
     */
    protected function generateSignature(): string
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');
        $secret = env('HOTEL_BEDS_SECRET');

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
        ])->post("{$this->baseUrl}/hotel-api/{$this->version}/hotels", [
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
                'language' => strtolower($request->language),
            ]);
    }

    /**
     * Get Hotel Details from HotelBeds API
     *
     * @param Request $request
     * @param string $hotelCode
     * @return \Illuminate\Http\Client\Response
     */
    public function getHotelDetails($request, string $hotelCode)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $language = $request->language ?? 'eng';

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->get("{$this->baseUrl}/hotel-content-api/{$this->version}/hotels/{$hotelCode}/details", [
            'language' => strtoupper($language)
        ]);
    }
}
