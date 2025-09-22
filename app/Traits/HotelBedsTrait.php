<?php

namespace App\Traits;

use App\Models\FavoriteHotel;
use App\Models\User;
use Illuminate\Http\Request;
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

    /**
     * Get favorite hotels for a user
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFavoriteHotels(Request $request, User $user)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $hotelCodes = FavoriteHotel::where('user_id', $user->id)->pluck('hotel_codes')->map(function ($code) {
            return (int) $code;
        })->toArray();

        $language = $request->language ?? 'eng';

        $hotels = Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->get("{$this->baseUrl}/hotel-content-api/{$this->version}/hotels", [
            'language' => strtoupper($language),
            'codes' => implode(',', $hotelCodes)
        ]);

        if ($hotels->successful()) {
            return $hotels->json();
        }

        return [];
    }

    /**
     * Confirm booking with HotelBeds API
     *
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     */
    public function bookingConfirmation($data)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $hotels = Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->post("{$this->baseUrl}/hotel-api/{$this->version}/bookings", [
            'holder' => [
                'name' => $data['first_name'],
                'surname' => $data['last_name']
            ],
            'rooms' => [
                [
                    'rateKey' => $data['rate_key']
                ]
            ],
            'clientReference' => 'booking-ref-' . $data['booking_id'],
            'remark' => $data['remark'] ?? 'Booking remarks are to be written here.',
            'tolerance' => 2,
        ]);

        if ($hotels->successful()) {
            return $hotels->json();
        }

        return [];
    }
}
