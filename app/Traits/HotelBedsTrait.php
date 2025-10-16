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

        $availableHotels = Http::withHeaders([
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

        if ($availableHotels->successful()) {
            $hotelData = [];
            $codes = [];
            foreach ($availableHotels['hotels']['hotels'] as $hotel) {
                $codes[] = $hotel['code'];
                $hotelData[$hotel['code']] = [
                    'code' => $hotel['code'],
                    'minRate' => $hotel['minRate'],
                    'maxRate' => $hotel['maxRate'],
                    'currency' => $hotel['currency'],
                    'categoryCode' => $hotel['categoryCode'],
                    'categoryName' => $hotel['categoryName'],
                    'zoneCode' => $hotel['zoneCode'],
                    'zoneName' => $hotel['zoneName'],
                    'latitude' => $hotel['latitude'],
                    'longitude' => $hotel['longitude'],
                ];
            }

            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 100);

            $language = $request->language ?? 'eng';

            $hotelContent = $this->hotelContentApiUsingCodes($codes, $page, $perPage, $language);

            foreach ($hotelContent['hotels'] as &$content) {
                if (isset($hotelData[$content['code']])) {
                    $content = array_merge($content, $hotelData[$content['code']]);
                }
            }

            return [
                'hotels' => $hotelContent['hotels'] ?? [],
                'checkIn' => $request->check_in,
                'checkOut' => $request->check_out,
                'total' => $availableHotels['hotels']['total']
            ];
        } else {
            if (isset($availableHotels['error']) && $availableHotels['error']) {
                throw new \Exception($availableHotels['error']);
            }
            throw new \Exception(__('messages.catch'));
        }
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
        $hotelCodes = FavoriteHotel::where('user_id', $user->id)->pluck('hotel_codes')->map(function ($code) {
            return (int) $code;
        })->toArray();

        return $this->hotelContentApiUsingCodes($hotelCodes, $request->get('page', 1), $request->get('per_page', 100), $request->language ?? 'eng');
    }

    /**
     * Fetch hotel content using hotel codes
     *
     * @param array $hotelCodes
     * @param int $page
     * @param int $perPage
     * @param string $language
     * @return void
     */
    public function hotelContentApiUsingCodes($hotelCodes, $page, $perPage, $language)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $hotels = Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->get("{$this->baseUrl}/hotel-content-api/{$this->version}/hotels", [
            'language' => strtoupper($language),
            'codes' => implode(',', $hotelCodes),
            'from' => ($page - 1) * $perPage,
        ]);

        if ($hotels->successful()) {
            return $hotels->json();
        }

        if (isset($hotels['error']) && $hotels['error']) {
            throw new \Exception($hotels['error']);
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
