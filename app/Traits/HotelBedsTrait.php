<?php

namespace App\Traits;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookingRoomCancellationPolicy;
use App\Models\FavoriteHotel;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

            if (isset($room['childrenAges']) && count($room['childrenAges']) > 0) {
                $paxes = [];
                foreach ($room['childrenAges'] as $childAge) {
                    $paxes[] = [
                        'type' => 'CH',
                        'age' => $childAge
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

            if (isset($availableHotels['hotels']['hotels'])) {
                foreach ($availableHotels['hotels']['hotels'] as $hotel) {
                    $codes[] = $hotel['code'];

                    $hotel_category = $hotel['categoryName'] ?? '';

                    $minNet = PHP_FLOAT_MAX;
                    $rateCurrency = 'SAR';
                    $tax_array = [];

                    // First pass: find min/max net values and get tax info
                    foreach ($hotel['rooms'] as $hotelRooms) {
                        foreach ($hotelRooms['rates'] as $rate) {
                            $netValue = (float) $rate['net'];
                            if ($netValue < $minNet) {
                                $minNet = $netValue;
                                // Get currency and tax info (assuming it's consistent across rates)
                                if (empty($tax_array) && isset($rate['taxes']['taxes'][0])) {
                                    $rateCurrency = $rate['taxes']['taxes'][0]['currency'];
                                    $tax_array = $rate['taxes']['taxes'];
                                }
                            }
                        }
                    }

                    // Only calculate prices twice (for min and max)
                    $minPrices = $this->calculatePrice($minNet, $hotel_category, $rateCurrency, $tax_array);

                    $hotelData[$hotel['code']] = [
                        'code' => $hotel['code'],

                        'minPrices' => $minPrices,

                        'minRate' => (string) round($minPrices['final_amount'], 2),
                        'maxRate' => (string) round($hotel['maxRate'], 2),
                        'currency' => $minPrices['converted_currency'],

                        'categoryCode' => $hotel['categoryCode'],
                        'categoryName' => $hotel['categoryName'],
                        'zoneCode' => $hotel['zoneCode'],
                        'zoneName' => $hotel['zoneName'],
                        'latitude' => $hotel['latitude'],
                        'longitude' => $hotel['longitude'],
                    ];
                }

                $page = $request->get('page', 1);
                $perPage = $request->get('per_page', $availableHotels->json()['hotels']['total']);
                $language = $request->language ?? 'eng';

                // Chunk codes into groups of 100
                $codeChunks = array_chunk($codes, 100);
                $allHotelContent = [];

                foreach ($codeChunks as $chunk) {
                    $hotelContent = $this->hotelContentApiUsingCodes($chunk, $page, $perPage, $language);

                    if (isset($hotelContent['hotels']) && is_array($hotelContent['hotels'])) {
                        $allHotelContent = array_merge($allHotelContent, $hotelContent['hotels']);
                    }
                }

                // Create a lookup array for quick access by hotel code
                $contentByCode = [];
                foreach ($allHotelContent as $content) {
                    if (isset($content['code'])) {
                        $contentByCode[$content['code']] = $content;
                    }
                }

                // Merge data in the original order from $codes
                $finalHotels = [];
                foreach ($codes as $code) {
                    if (isset($contentByCode[$code])) {
                        $hotel = $contentByCode[$code];

                        // Merge with availability data
                        if (isset($hotelData[$code])) {
                            $hotel = array_merge($hotel, $hotelData[$code]);
                        }

                        $finalHotels[] = $hotel;
                    }
                }

                return [
                    'hotels' => $finalHotels,
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

                if (isset($room['childrenAges']) && count($room['childrenAges']) > 0) {
                    $paxes = [];
                    foreach ($room['childrenAges'] as $childAge) {
                        $paxes[] = [
                            'type' => 'CH',
                            'age' => $childAge
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

            $hotel_category = '';
            if (isset($hotel_content['category']) && isset($hotel_content['category']['description'])) {
                $hotel_category = $hotel_content['category']['description']['content'];
            }

            if ($availableHotels->successful()) {
                if (isset($availableHotels->json()['hotels']['hotels']) && isset($availableHotels->json()['hotels']['hotels'][0]['rooms'])) {
                    $availabilityRooms = $availableHotels->json()['hotels']['hotels'][0]['rooms'];

                    // Use reference for the outer loop as well
                    foreach ($availabilityRooms as &$availabilityRoom) {
                        foreach ($availabilityRoom['rates'] as &$rate) {
                            $rateCurrency = 'SAR';

                            $tax_array = [];
                            if (isset($rate['taxes']) && isset($rate['taxes']['taxes']) && isset($rate['taxes']['taxes'][0]['currency'])) {
                                $rateCurrency = $rate['taxes']['taxes'][0]['currency'];
                                $tax_array = $rate['taxes']['taxes'];
                            }

                            $prices = $this->calculatePrice($rate['net'], $hotel_category, $rateCurrency, $tax_array);

                            $rate['prices'] = $prices;
                            $rate['net'] = (string) round($prices['final_amount'], 2);
                            $rate['currency'] = $prices['converted_currency'];
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
     * @return array
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
            'rooms' => $data['rate_keys'],
            'clientReference' => $data['order'],
            'remark' => $data['remark'] ?? null,
            'tolerance' => 5,
        ]);

        if ($hotels->successful()) {

            Booking::where('id', $data['booking_id'])->update([
                'booking_reference' => $hotels->json()['booking']['reference'],
            ]);

            return [
                'status' => true,
                'data' => $hotels->json()
            ];
        } else {

            $booking = Booking::where('id', $data['booking_id'])->first();

            $booking->update([
                'status' => 'cancelled',
            ]);

            Http::withToken(env('TAP_SECRET'))
                ->acceptJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.tap.company/v2/refunds', [
                    'charge_id' => $booking->tap_charge_id,
                    'amount' => $booking->total_price - $booking->discount_amount,
                    'currency' => $booking->currency,
                    'reason' => 'Booking order ' . $booking->order . ' cancelled by user',
                ]);
        }

        Log::error('HotelBeds Booking Confirmation Failed', $hotels->json());

        return [];
    }

    /**
     * Get locations and destinations
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Client\Response
     */
    public function getLocationsAndDestinations($request)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $locations = Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->get("{$this->baseUrl}/hotel-content-api/{$this->version}/locations/destinations");

        if ($locations->successful()) {
            return $locations->json();
        }

        return [];
    }

    public function checkRoomAvailability($room_rates)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $hotels = Http::withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->post("{$this->baseUrl}/hotel-api/{$this->version}/checkrates", [
            'rooms' => array_map(function ($rateKey) {
                return ['rateKey' => $rateKey];
            }, $room_rates)
        ]);

        if ($hotels->successful()) {
            $hotels = $hotels->json();

            foreach ($hotels['hotel']['rooms'] as $room) {
                foreach ($room['rates'] as $rate) {
                    $bookingRoom = BookingRoom::where('rate_key', $rate['rateKey'])->first();

                    $bookingRoom->update([
                        'amount' => $rate['net'],
                    ]);
                    foreach ($rate['cancellationPolicies'] as $policy) {
                        if ($bookingRoom) {
                            BookingRoomCancellationPolicy::updateOrCreate([
                                'booking_room_id' => $bookingRoom->id,
                                'amount' => $policy['amount'],
                                'from' => $policy['from'],
                            ]);
                        }
                    }
                }
            }
            return $hotels;
        }

        throw new \Exception(__('messages.catch'));
    }

    public function cancelBooking($booking)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        try {
            $hotels = Http::withHeaders([
                'Accept' => 'application/json',
                'Api-key' => $apiKey,
                'X-Signature' => $this->generateSignature(),
            ])->delete("{$this->baseUrl}/hotel-api/{$this->version}/bookings/{$booking->booking_reference}");

            if ($hotels->successful()) {

                Log::info('HotelBeds Booking Cancellation Successful', $hotels->json());

                Booking::where('id', $booking->id)->update([
                    'status' => 'cancelled',
                ]);

                // Calculate refund amount based on cancellation policies
                $refundAmount = 0;
                foreach ($booking->booking_room as $bookingRoom) {
                    $bookingRoomCancellationPolicy = BookingRoomCancellationPolicy::where('booking_room_id', $bookingRoom->id)
                        ->where('from', '<=', now())
                        ->orderBy('from', 'desc')
                        ->first();

                    if ($bookingRoomCancellationPolicy) {
                        $refundAmount += $bookingRoomCancellationPolicy->amount;
                    } else {
                        // No policy exists - full refund for this room
                        $refundAmount += $bookingRoom->amount;
                    }
                }

                if ($refundAmount > 0) {
                    $refunds = Http::withToken(env('TAP_SECRET'))
                        ->acceptJson()
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                        ])
                        ->post('https://api.tap.company/v2/refunds', [
                            'charge_id' => $booking->tap_charge_id,
                            'amount' => $refundAmount,
                            'currency' => $booking->currency,
                            'reason' => 'Booking order ' . $booking->order . ' cancelled by user',
                        ]);

                    if ($refunds->successful()) {
                        Booking::where('id', $booking->id)->update([
                            'refunded_amount' => $refundAmount,
                        ]);
                    } else {
                        Log::error('HotelBeds Booking Cancellation Refund Failed', $refunds->json());
                    }
                }

                return [
                    'status' => true,
                    'message' => __('messages.booking.cancelled'),
                    'data' => $hotels->json(),
                ];
            }

            return [
                'status' => false,
                'message' => __('messages.catch'),
                'data' => [],
            ];
        } catch (Exception $e) {
            Log::error('HotelBeds Booking Cancellation Failed', ['error' => $e->getMessage()]);

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
}
