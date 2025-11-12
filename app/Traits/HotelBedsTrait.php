<?php

namespace App\Traits;

use App\Models\FavoriteHotel;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

trait HotelBedsTrait
{
    use CurrencyConversion;

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

        $rooms = [];
        foreach ($request->rooms as $room) {
            $roomData = [
                'rooms' => 1,
                'adults' => $room['adults'],
                'children' => $room['children'] ?? 0,
            ];

            if (isset($room['children']) && $room['children'] > 0) {
                $paxes = [];
                for ($i = 0; $i < $room['children']; $i++) {
                    $paxes[] = [
                        'type' => 'CH',
                        'age' => 11
                    ];
                }
                $roomData['paxes'] = $paxes;
            }

            $rooms[] = $roomData;
        }

        $payload = [
            'stay' => [
                'checkIn' => $request->check_in,
                'checkOut' => $request->check_out
            ],
            'occupancies' => $rooms,
            'geolocation' => [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => 50,
                'unit' => 'km'
            ],
            'language' => strtolower($request->language),
            'filter' => [
                'maxHotels' => 50
            ]
        ];

        if ($request->has('star_rating')) {
            $payload['filter']['minCategory'] = $request->star_rating;
            $payload['filter']['maxCategory'] = $request->star_rating;
        }

        if ($request->has('min_price')) {
            $payload['filter']['minRate'] = $request->min_price;
        }

        if ($request->has('max_price')) {
            $payload['filter']['maxRate'] = $request->max_price;
        }

        if ($request->has('accommodations')) {
            $payload['accommodations'] = explode(',', $request->accommodations);
        }

        $availableHotels = Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->post("{$this->baseUrl}/hotel-api/{$this->version}/hotels", $payload);

        if ($availableHotels->successful()) {
            $hotelData = [];
            $codes = [];

            $setting = Setting::first();

            $desiredCurrency = "SAR";

            if (isset($availableHotels['hotels']['hotels'])) {
                foreach ($availableHotels['hotels']['hotels'] as $hotel) {
                    $codes[] = $hotel['code'];

                    try {
                        $convertedPrices = $this->getUpdatedExchangeRates($hotel['currency'], $desiredCurrency);
                    } catch (Exception $e) {
                        $convertedPrices = 1; // Fallback to 1 if conversion fails
                        $desiredCurrency = $hotel['currency'];
                    }

                    $commission_percentage = 0;
                    if ($hotel['categoryName'] == '5 STARS') {
                        $commission_percentage = $setting->five_star_commission;
                    } elseif ($hotel['categoryName'] == '4 STARS') {
                        $commission_percentage = $setting->four_star_commission;
                    } elseif ($hotel['categoryName'] == '3 STARS') {
                        $commission_percentage = $setting->three_star_commission;
                    } elseif ($hotel['categoryName'] == '2 STARS') {
                        $commission_percentage = $setting->two_star_commission;
                    } elseif ($hotel['categoryName'] == '1 STAR') {
                        $commission_percentage = $setting->one_star_commission;
                    }

                    $hotelData[$hotel['code']] = [
                        'code' => $hotel['code'],
                        'originalMinRate' => $hotel['minRate'],
                        'originalMaxRate' => $hotel['maxRate'],
                        'originalCurrency' => $hotel['currency'],

                        'convertedMinRate' => (string) round(($hotel['minRate'] * $convertedPrices), 2),
                        'convertedMaxRate' => (string) round(($hotel['maxRate'] * $convertedPrices), 2),

                        'minRate' => (string) round((($hotel['minRate'] * $convertedPrices) * (1 + ($commission_percentage / 100))), 2),
                        'maxRate' => (string) round((($hotel['maxRate'] * $convertedPrices) * (1 + ($commission_percentage / 100))), 2),
                        'currency' => $desiredCurrency,

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
                return [
                    'hotels' => [],
                    'checkIn' => $request->check_in,
                    'checkOut' => $request->check_out,
                    'total' => $availableHotels['hotels']['total']
                ];
            }
        } else {
            if (isset($availableHotels['error']) && is_array($availableHotels['error']) && isset($availableHotels['error']['message'])) {
                throw new \Exception($availableHotels['error']['message']);
            }
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

        $checkIn = $request->check_in ?? Carbon::tomorrow()->format('Y-m-d');
        $checkOut = $request->check_out ?? Carbon::tomorrow()->addDays(1)->format('Y-m-d');

        $rooms = [];
        if ($request->rooms) {
            foreach ($request->rooms as $room) {
                $roomData = [
                    'rooms' => 1,
                    'adults' => $room['adults'],
                    'children' => $room['children'] ?? 0,
                ];

                if (isset($room['children']) && $room['children'] > 0) {
                    $paxes = [];
                    for ($i = 0; $i < $room['children']; $i++) {
                        $paxes[] = [
                            'type' => 'CH',
                            'age' => 11
                        ];
                    }
                    $roomData['paxes'] = $paxes;
                }

                $rooms[] = $roomData;
            }
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->get("{$this->baseUrl}/hotel-content-api/{$this->version}/hotels/{$hotelCode}/details", [
            'language' => strtoupper($language)
        ]);

        if ($response->successful()) {

            $hotel_content = $response->json()['hotel'];

            $availableHotels = Http::withHeaders([
                'Accept' => 'application/json',
                'Api-key' => $apiKey,
                'X-Signature' => $this->generateSignature(),
            ])->post("{$this->baseUrl}/hotel-api/{$this->version}/hotels", [
                'stay' => [
                    'checkIn' => $checkIn,
                    'checkOut' => $checkOut
                ],
                'occupancies' => $rooms,
                'language' => strtolower($request->language),
                'hotels' => [
                    'hotel' => [
                        $hotel_content['code']
                    ]
                ]
            ]);

            $setting = Setting::first();

            $commission_percentage = 0;
            if (isset($hotel_content['category']) && isset($hotel_content['category']['description'])) {
                if ($hotel_content['category']['description']['content'] == '5 STARS') {
                    $commission_percentage = $setting->five_star_commission;
                } elseif ($hotel_content['category']['description']['content'] == '4 STARS') {
                    $commission_percentage = $setting->four_star_commission;
                } elseif ($hotel_content['category']['description']['content'] == '3 STARS') {
                    $commission_percentage = $setting->three_star_commission;
                } elseif ($hotel_content['category']['description']['content'] == '2 STARS') {
                    $commission_percentage = $setting->two_star_commission;
                } elseif ($hotel_content['category']['description']['content'] == '1 STAR') {
                    $commission_percentage = $setting->one_star_commission;
                }
            }

            $desiredCurrency = "SAR";

            if ($availableHotels->successful()) {
                if (isset($availableHotels->json()['hotels']['hotels']) && isset($availableHotels->json()['hotels']['hotels'][0]['rooms'])) {
                    $availabilityRooms = $availableHotels->json()['hotels']['hotels'][0]['rooms'];

                    // Use reference for the outer loop as well
                    foreach ($availabilityRooms as &$availabilityRoom) {
                        foreach ($availabilityRoom['rates'] as &$rate) {
                            $rateCurrency = 'SAR';

                            $taxes = 0;
                            if (isset($rate['taxes']) && isset($rate['taxes']['taxes']) && isset($rate['taxes']['taxes'][0]['currency'])) {
                                $rateCurrency = $rate['taxes']['taxes'][0]['currency'];

                                foreach ($rate['taxes']['taxes'] as $tax) {
                                    $taxes += $tax['amount'];
                                }
                            }

                            try {
                                $convertedPrices = $this->getUpdatedExchangeRates($rateCurrency, $desiredCurrency);
                            } catch (Exception $e) {
                                $convertedPrices = 1;
                                $desiredCurrency = $rateCurrency;
                            }

                            $netWithOutCommission = round(($rate['net'] * $convertedPrices), 2);

                            if ($taxes > 0) {
                                $taxes = round(($taxes * $convertedPrices), 2);
                            }

                            $commissionAmount = 0;
                            if ($commission_percentage > 0) {
                                $commissionAmount = round(($netWithOutCommission * ($commission_percentage / 100)), 2);
                            }

                            $rate['originalNet'] = $rate['net'];
                            $rate['convertedRate'] = (string) $netWithOutCommission;
                            $rate['taxesRate'] = (string) $taxes;
                            $rate['commission_percentage'] = (string) $commission_percentage;
                            $rate['commissionAmount'] = (string) $commissionAmount;
                            $rate['net'] = (string) round(($netWithOutCommission + $commissionAmount + $taxes), 2);
                            $rate['currency'] = $desiredCurrency;
                        }
                        unset($rate); // Unset the inner loop reference
                    }
                    unset($availabilityRoom); // Unset the outer loop reference

                    // Create a map of content rooms by code for quick lookup
                    $contentRoomsMap = collect($hotel_content['rooms'] ?? [])
                        ->filter(fn($room) => isset($room['roomCode']))
                        ->keyBy('roomCode');

                    // Start with availability rooms and add missing fields from content rooms
                    $hotel_content['rooms'] = collect($availabilityRooms)->map(function ($availabilityRoom) use ($contentRoomsMap) {
                        // Skip if availability room doesn't have a code
                        if (!isset($availabilityRoom['code'])) {
                            return $availabilityRoom;
                        }

                        $roomCode = $availabilityRoom['code'];

                        // If this room exists in content, merge (content fields first, then availability overrides)
                        if ($contentRoomsMap->has($roomCode)) {
                            return array_merge($contentRoomsMap->get($roomCode), $availabilityRoom);
                        }

                        return $availabilityRoom;
                    })->toArray();
                }
            }

            return [
                'hotel' => $hotel_content,
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'rooms' => $rooms,
            ];
        }

        throw new \Exception(__('messages.catch'));
    }

    /**
     * Get Accommodation Types from HotelBeds API
     *
     * @return \Illuminate\Http\Client\Response
     */
    public function getAccommodationTypes()
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $language = $request->language ?? 'eng';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->get("{$this->baseUrl}/hotel-content-api/{$this->version}/types/accommodations", [
            'language' => strtoupper($language)
        ]);

        return $response->json()['accommodations'] ?? [];
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
