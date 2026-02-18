<?php

namespace App\Traits;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Facility;
use App\Models\FavoriteHotel;
use App\Models\FeaturedHotel;
use App\Models\Hotel;
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
    protected $apiMappingEntity = 2716;

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
     * Get available hotels from HotelBeds API.
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    protected function checkHotelAvailabilityNew($request)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $destinationCode = $request->destination_code;

        if ($destinationCode) {
            $localHotels = Hotel::where('destination_code', $destinationCode)
                ->with(['first_image', 'facilities'])
                ->where('status', 1)
                ->limit(2000)
                ->get();
        } else {
            $localHotels = Hotel::where('code', $request->hotel_code)
                ->with(['first_image', 'facilities'])
                ->where('status', 1)
                ->get();
        }

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

        // sourceMarket
        $payload = [
            'stay' => [
                'checkIn' => $request->check_in,
                'checkOut' => $request->check_out
            ],
            'occupancies' => $rooms,
            'hotels' => [
                'hotel' => $localHotels->pluck('code')->toArray(),
            ],
            'language' => strtolower($request->language),
            'sourceMarket' => 'SA'
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
            $zones = [];
            $facilities = [];

            if (isset($availableHotels['hotels']['hotels'])) {
                // Extract to a plain array before modifying
                $hotelsData = $availableHotels['hotels']['hotels'];

                foreach ($hotelsData as &$hotel) {
                    if (!isset($zones[$hotel['zoneCode']])) {
                        $zones[$hotel['zoneCode']] = [
                            'code' => $hotel['zoneCode'],
                            'name' => $hotel['zoneName'],
                            'count' => 0,
                        ];
                    }
                    $zones[$hotel['zoneCode']]['count']++;

                    $minPrices = $this->calculatePrice($hotel['minRate'], $hotel['categoryCode'], $hotel['currency']);

                    $hotel['minRate'] = $minPrices['final_amount'];
                    $hotel['currency'] = $minPrices['converted_currency'];

                    $localHotel = $localHotels->where('code', $hotel['code'])->first();
                    $hotel['accommodationTypeCode'] = $localHotel ? $localHotel->accommodation_type_code : null;
                    $hotel['address'] = ["content" => $localHotel ? $localHotel->address : null];
                    $hotel['city'] = ["content" => $localHotel ? $localHotel->city : null];
                    $hotel['images'] = [
                        [
                            "path" => $localHotel ? $localHotel->first_image->path : null,
                            "imageTypeCode" => $localHotel ? $localHotel->first_image->image_type_code : null,
                        ]
                    ];
                    $hotel['facilities'] = $localHotel ? $localHotel->facilities->map(function ($facility) {
                        return [
                            'facilityCode' => $facility->facility_code,
                            'facilityGroupCode' => $facility->facility_group_code,
                        ];
                    })->toArray() : [];

                    if ($localHotel && $localHotel->facilities) {
                        foreach ($localHotel->facilities as $facility) {
                            $code = $facility->facility_code;
                            if (!isset($facilities[$code])) {
                                $facilities[$code] = 0;
                            }
                            $facilities[$code]++;
                        }
                    }
                }
                unset($hotel);

                // feature hotels should be at the top
                $featuredHotelCodes = FeaturedHotel::orderBy('show_tag', 'desc')->pluck('show_tag', 'hotel_code')->toArray();

                // Add featured field to each hotel
                foreach ($hotelsData as $key => &$hotel) {
                    $hotel['featured'] = array_key_exists($hotel['code'], $featuredHotelCodes);
                    $hotel['show_tag'] = isset($featuredHotelCodes[$hotel['code']]) && $featuredHotelCodes[$hotel['code']] ? true : false;
                }
                unset($hotel); // Break the reference

                usort($hotelsData, function ($a, $b) {
                    // Priority 1: featured = true AND show_tag = true
                    $aPriority1 = $a['featured'] && $a['show_tag'];
                    $bPriority1 = $b['featured'] && $b['show_tag'];

                    if ($aPriority1 !== $bPriority1) {
                        return $bPriority1 ? 1 : -1;
                    }

                    // Priority 2: featured = true AND show_tag = false
                    $aPriority2 = $a['featured'] && !$a['show_tag'];
                    $bPriority2 = $b['featured'] && !$b['show_tag'];

                    if ($aPriority2 !== $bPriority2) {
                        return $bPriority2 ? 1 : -1;
                    }

                    // Priority 3: rest of the hotels (not featured)
                    return 0;
                });

                $facilityCounts = $facilities; // associative: [code => count]

                $facilities = Facility::whereNotIn('name', ['1', '4', 'LGTBIQ friendly', 'LGBTQ friendly'])
                    ->whereIn('code', array_keys($facilityCounts))
                    ->get()
                    ->toArray();

                // Sort zones by count descending
                usort($zones, fn($a, $b) => $b['count'] <=> $a['count']);

                // Sort facilities by count descending
                usort($facilities, fn($a, $b) => ($facilityCounts[$b['code']] ?? 0) <=> ($facilityCounts[$a['code']] ?? 0));

                return [
                    'hotels' => $hotelsData,
                    'checkIn' => $request->check_in,
                    'checkOut' => $request->check_out,
                    'total' => $availableHotels['hotels']['total'],
                    'zones' => $zones,
                    'facilities' => array_map(function ($facility) use ($facilityCounts) {
                        return [
                            'code' => $facility['code'],
                            'facility_group_code' => $facility['facility_group_code'],
                            'name' => $facility['name'],
                            'count' => $facilityCounts[$facility['code']] ?? 0,
                        ];
                    }, $facilities)
                ];
            } else {
                return [
                    'hotels' => [],
                    'checkIn' => $request->check_in,
                    'checkOut' => $request->check_out,
                    'total' => $availableHotels['hotels']['total'],
                    'zones' => [],
                    'facilities' => []
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
     * Make HotelBeds API request
     *
     * @param Request $request
     * @return \Illuminate\Http\Client\Response | array
     */
    protected function checkHotelAvailability($request)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $destinationCode = $request->destination_code;

        if ($destinationCode) {
            $hotelCodes = Hotel::where('destination_code', $destinationCode)
                ->where('status', 1)
                ->limit(2000)
                ->pluck('code')
                ->toArray();
        } else {
            $hotelCodes = [$request->hotel_code];
        }

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

        // sourceMarket
        $payload = [
            'stay' => [
                'checkIn' => $request->check_in,
                'checkOut' => $request->check_out
            ],
            'occupancies' => $rooms,
            'hotels' => [
                'hotel' => $hotelCodes
            ],
            'language' => strtolower($request->language),
            'sourceMarket' => 'SA'
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
            $zones = [];
            $facilities = [];

            if (isset($availableHotels['hotels']['hotels'])) {
                foreach ($availableHotels['hotels']['hotels'] as $hotel) {
                    $codes[] = $hotel['code'];

                    if (!isset($zones[$hotel['zoneCode']])) {
                        $zones[$hotel['zoneCode']] = [
                            'code' => $hotel['zoneCode'],
                            'name' => $hotel['zoneName'],
                            'count' => 0,
                        ];
                    }
                    $zones[$hotel['zoneCode']]['count']++;

                    $hotel_category = $hotel['categoryName'] ?? '';

                    $minNet = PHP_FLOAT_MAX;
                    $rateCurrency = 'EUR';
                    $tax_array = [];

                    // First pass: find min/max net values and get tax info
                    foreach ($hotel['rooms'] as $hotelRooms) {
                        foreach ($hotelRooms['rates'] as $rate) {
                            $netValue = (float) $rate['net'];
                            if ($netValue < $minNet) {
                                $minNet = $netValue;
                                // Get currency and tax info (assuming it's consistent across rates)
                                if (empty($tax_array) && isset($rate['taxes']['taxes'][0])) {
                                    $rateCurrency = $rate['taxes']['taxes'][0]['clientCurrency'];
                                }
                            }
                        }
                    }

                    // Only calculate prices twice (for min and max)
                    $minPrices = $this->calculatePrice($minNet, $hotel_category, $rateCurrency, $hotel['code'], $hotel['destinationName']);

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
                    if (isset($content['facilities']) && is_array($content['facilities'])) {
                        foreach ($content['facilities'] as $facility) {
                            $code = $facility['facilityCode'];
                            if (!isset($facilities[$code])) {
                                $facilities[$code] = 0;
                            }
                            $facilities[$code]++;
                        }
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

                // feature hotels should be at the top
                $featuredHotelCodes = FeaturedHotel::orderBy('show_tag', 'desc')->pluck('show_tag', 'hotel_code')->toArray();

                // Add featured field to each hotel
                foreach ($finalHotels as $key => &$hotel) {
                    $hotel['featured'] = array_key_exists($hotel['code'], $featuredHotelCodes);
                    $hotel['show_tag'] = isset($featuredHotelCodes[$hotel['code']]) && $featuredHotelCodes[$hotel['code']] ? true : false;
                }
                unset($hotel); // Break the reference

                usort($finalHotels, function ($a, $b) {
                    // Priority 1: featured = true AND show_tag = true
                    $aPriority1 = $a['featured'] && $a['show_tag'];
                    $bPriority1 = $b['featured'] && $b['show_tag'];

                    if ($aPriority1 !== $bPriority1) {
                        return $bPriority1 ? 1 : -1;
                    }

                    // Priority 2: featured = true AND show_tag = false
                    $aPriority2 = $a['featured'] && !$a['show_tag'];
                    $bPriority2 = $b['featured'] && !$b['show_tag'];

                    if ($aPriority2 !== $bPriority2) {
                        return $bPriority2 ? 1 : -1;
                    }

                    // Priority 3: rest of the hotels (not featured)
                    return 0;
                });

                $facilityCounts = $facilities; // associative: [code => count]

                $facilities = Facility::whereNotIn('name', ['1', 'LGTBIQ friendly', 'LGBTQ friendly'])
                    ->whereIn('code', array_keys($facilityCounts))
                    ->get()
                    ->toArray();

                // Sort zones by count descending
                usort($zones, fn($a, $b) => $b['count'] <=> $a['count']);

                // Sort facilities by count descending
                usort($facilities, fn($a, $b) => ($facilityCounts[$b['code']] ?? 0) <=> ($facilityCounts[$a['code']] ?? 0));

                return [
                    'hotels' => $finalHotels,
                    'checkIn' => $request->check_in,
                    'checkOut' => $request->check_out,
                    'total' => $availableHotels['hotels']['total'],
                    'zones' => $zones,
                    'facilities' => array_map(function ($facility) use ($facilityCounts) {
                        return [
                            'code' => $facility['code'],
                            'facility_group_code' => $facility['facility_group_code'],
                            'name' => $facility['name'],
                            'count' => $facilityCounts[$facility['code']] ?? 0,
                        ];
                    }, $facilities)
                ];
            } else {
                return [
                    'hotels' => [],
                    'checkIn' => $request->check_in,
                    'checkOut' => $request->check_out,
                    'total' => $availableHotels['hotels']['total'],
                    'zones' => [],
                    'facilities' => []
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
        if ($request->rooms && is_array($request->rooms)) {
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
            'Api-Mapping-Entity' => $this->apiMappingEntity,
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

                        /* Remove rate with packaging: true */
                        if (isset($availabilityRoom['rates']) && is_array($availabilityRoom['rates'])) {
                            $availabilityRoom['rates'] = array_filter($availabilityRoom['rates'], function ($rate) {
                                return !isset($rate['packaging']) || $rate['packaging'] === false;
                            });
                        }

                        foreach ($availabilityRoom['rates'] as &$rate) {
                            $rateCurrency = 'EUR';

                            if (isset($rate['taxes']) && isset($rate['taxes']['taxes']) && isset($rate['taxes']['taxes'][0]['clientCurrency'])) {
                                $rateCurrency = $rate['taxes']['taxes'][0]['clientCurrency'];
                            }

                            $prices = $this->calculatePrice($rate['net'], $hotel_category, $rateCurrency, $hotel_content['code'], $hotel_content['city']['content']);

                            $rate['prices'] = $prices;
                            $rate['net'] = (string) round($prices['final_amount'], 2);
                            $rate['currency'] = $prices['converted_currency'];
                        }
                        unset($rate); // Unset the inner loop reference

                        // Filter rates: group by boardCode, keep only lowest price for refundable and non-refundable
                        $availabilityRoom['rates'] = $this->filterRatesByBoardCode($availabilityRoom['rates']);
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
     * Filter rates by boardCode, keeping only the lowest price for refundable and non-refundable options
     */
    private function filterRatesByBoardCode(array $rates): array
    {
        // Group rates by boardCode
        $groupedRates = collect($rates)->groupBy('boardCode');

        $filteredRates = [];

        foreach ($groupedRates as $boardCode => $boardRates) {
            // Separate into refundable and non-refundable
            $nonRefundable = $boardRates->filter(function ($rate) {
                return isset($rate['rateClass']) && $rate['rateClass'] === 'NRF';
            });

            $refundable = $boardRates->filter(function ($rate) {
                return !isset($rate['rateClass']) || $rate['rateClass'] !== 'NRF';
            });

            // Get the lowest price non-refundable rate
            if ($nonRefundable->isNotEmpty()) {
                $lowestNonRefundable = $nonRefundable->sortBy(function ($rate) {
                    return (float) $rate['net'];
                })->first();

                $filteredRates[] = $lowestNonRefundable;
            }

            // Get the lowest price refundable rate
            if ($refundable->isNotEmpty()) {
                $lowestRefundable = $refundable->sortBy(function ($rate) {
                    return (float) $rate['net'];
                })->first();

                $filteredRates[] = $lowestRefundable;
            }
        }

        // Sort the filtered rates by net price
        $filteredRates = collect($filteredRates)->sortBy(function ($rate) {
            return (float) $rate['net'];
        })->values()->toArray();

        return $filteredRates;
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
            'Api-Mapping-Entity' => $this->apiMappingEntity,
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
            'Api-Mapping-Entity' => $this->apiMappingEntity,
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

            Log::info('HotelBeds Booking Confirmation Success', $hotels->json());

            $hotelBooking = $hotels->json();

            $supplierName = $hotelBooking['booking']['hotel']['supplier']['name'];
            $vatNumber = $hotelBooking['booking']['hotel']['supplier']['vatNumber'];

            Booking::where('id', $data['booking_id'])->update([
                'booking_reference' => $hotelBooking['booking']['reference'],
                'supplier_name' => $supplierName,
                'vat_number' => $vatNumber
            ]);

            return [
                'status' => true,
                'data' => $hotelBooking
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
            'Api-Mapping-Entity' => $this->apiMappingEntity,
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
            return $hotels;
        }

        throw new \Exception(__('messages.catch'));
    }

    public function bookingDetails($booking_reference)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        try {
            $hotels = Http::withHeaders([
                'Accept' => 'application/json',
                'Api-key' => $apiKey,
                'X-Signature' => $this->generateSignature(),
            ])->get("{$this->baseUrl}/hotel-api/{$this->version}/bookings/{$booking_reference}");

            if ($hotels->successful()) {
                return $hotels->json();
            }
        } catch (Exception $e) {
            Log::error('HotelBeds Booking Details Fetch Failed', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    public function cancelBooking($booking)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        try {
            $hotelDetail = $this->bookingDetails($booking->booking_reference);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        try {
            $refundAmount = $hotelDetail['booking']['pendingAmount'];
            $currency = $hotelDetail['booking']['currency'];

            $bookingRoomNRF = BookingRoom::where('booking_id', $booking->id)
                ->where('rate_class', 'NRF')
                ->exists();

            if ($bookingRoomNRF) {
                Booking::where('id', $booking->id)->update([
                    'status' => 'cancelled',
                    'refunded_amount' => 0,
                    'refunded_currency' => null,
                    'cancellation_in_progress' => true,
                ]);

                return [
                    'status' => true,
                    'message' => 'Booking cancelled successfully',
                    'data' => [],
                ];
            }

            $prices = $this->calculatePrice($refundAmount, $hotelDetail['booking']['hotel']['categoryName'], $currency, $hotelDetail['booking']['hotel']['code'], $hotelDetail['booking']['hotel']['destinationName']);

            // reduce decimal points to 2
            $amountToRefund = round($prices['converted_amount'], 2);

            if ($amountToRefund > 0) {

                if ($amountToRefund > $booking->total_price) {
                    $amountToRefund = $booking->total_price;
                }

                $hotels = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Api-key' => $apiKey,
                    'X-Signature' => $this->generateSignature(),
                ])->delete("{$this->baseUrl}/hotel-api/{$this->version}/bookings/{$booking->booking_reference}");

                if ($hotels->successful()) {

                    Booking::where('id', $booking->id)->update([
                        'status' => 'cancelled',
                        'refunded_amount' => $amountToRefund,
                        'refunded_currency' => $prices['converted_currency'],
                        'cancellation_in_progress' => true,
                    ]);

                    return [
                        'status' => true,
                        'message' => 'Booking cancelled successfully',
                        'data' => [],
                    ];

                } else {
                    Log::error('Failed to cancel pending booking', [
                        'booking_reference' => $booking->booking_reference,
                        'response' => $hotels->json(),
                    ]);
                }
            }

            Log::error('HotelBeds Booking Cancellation Failed 1', [
                'booking_reference' => $booking->booking_reference,
                'refund_amount' => $amountToRefund,
                'currency' => $prices['converted_currency'],
            ]);

            return [
                'status' => false,
                'message' => 'Refund amount is zero or invalid',
                'data' => [],
            ];

        } catch (Exception $e) {
            Log::error('HotelBeds Booking Cancellation Failed 2', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
}
