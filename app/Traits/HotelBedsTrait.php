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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait HotelBedsTrait
{
    use CurrencyConversion;

    protected $baseUrl;
    protected $contentApiUrl;
    protected $version = '1.0';
    protected $apiMappingEntity = 2716;

    function __construct()
    {
        $this->initializeUrls();
    }

    /**
     * Initialize the URLs from environment variables
     */
    private function initializeUrls()
    {
        if (empty($this->baseUrl)) {
            $this->baseUrl = env('HOTEL_BEDS_BASE_URL');
        }
        if (empty($this->contentApiUrl)) {
            $this->contentApiUrl = env('HOTEL_BEDS_CONTENT_API_URL');
        }
    }

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
        // Ensure URLs are initialized
        $this->initializeUrls();

        $apiKey = env('HOTEL_BEDS_API_KEY');
        $destinationCode = $request->destination_code;

        // 1. Build base query
        $hotelQuery = Hotel::where('status', 1)
            ->select(['code', 'accommodation_type_code', 'address', 'city']);

        if ($destinationCode) {
            $hotelQuery->where('destination_code', $destinationCode)->limit(2000);
        } else {
            $hotelQuery->where('code', $request->hotel_code);
        }

        if ($request->has('star_rating')) {
            $hotelQuery->where(function ($query) use ($request) {
                foreach (explode(',', $request->star_rating) as $star) {
                    $query->orWhere('category_code', 'LIKE', trim($star) . "%");
                }
            });
        }

        // Create star rating suffix for cache keys
        $starRatingSuffix = $request->has('star_rating') ? '_stars_' . str_replace(',', '_', $request->star_rating) : '';

        // 2. Cache hotel codes (used for API payload)
        $hotelCodesCacheKey = $destinationCode
            ? "hotel_codes_dest_{$destinationCode}{$starRatingSuffix}"
            : "hotel_codes_single_{$request->hotel_code}{$starRatingSuffix}";

        $hotelCodes = Cache::rememberForever($hotelCodesCacheKey, function () use ($hotelQuery) {
            return (clone $hotelQuery)->pluck('code')->toArray();
        });

        // 3. Cache local hotels map (keyed by code for O(1) lookup)
        $localHotelsMapCacheKey = $destinationCode
            ? "local_hotels_map_dest_{$destinationCode}{$starRatingSuffix}"
            : "local_hotels_map_single_{$request->hotel_code}{$starRatingSuffix}";

        $localHotelsMap = Cache::rememberForever($localHotelsMapCacheKey, function () use ($hotelQuery) {
            return (clone $hotelQuery)->get()->keyBy('code')->toArray();
        });

        // 4. Cache first images keyed by hotel code
        $firstImagesCacheKey = $destinationCode
            ? "hotel_images_dest_{$destinationCode}{$starRatingSuffix}"
            : "hotel_images_single_{$request->hotel_code}{$starRatingSuffix}";

        $firstImages = Cache::rememberForever($firstImagesCacheKey, function () use ($hotelCodes) {
            return DB::table('hotel_images')
                ->whereIn('hotel_code', $hotelCodes)
                ->select(['hotel_code', 'path', 'image_type_code'])
                ->get()
                ->keyBy('hotel_code');
        });

        // 5. Cache facilities grouped by hotel code
        $facilitiesCacheKey = $destinationCode
            ? "hotel_facilities_dest_{$destinationCode}{$starRatingSuffix}"
            : "hotel_facilities_single_{$request->hotel_code}{$starRatingSuffix}";

        $facilitiesByHotel = Cache::rememberForever($facilitiesCacheKey, function () use ($hotelCodes) {
            return DB::table('hotel_facilities')
                ->whereIn('hotel_code', $hotelCodes)
                ->select(['hotel_code', 'facility_code', 'facility_group_code'])
                ->get()
                ->groupBy('hotel_code');
        });

        // 6. Cache featured hotels (changes rarely)
        $featuredHotelCodes = FeaturedHotel::orderBy('show_tag', 'desc')
                ->pluck('show_tag', 'hotel_code')
                ->toArray();

        // 7. Build rooms payload (no DB involved, no caching needed)
        $rooms = [];
        foreach ($request->rooms as $room) {
            $roomData = [
                'rooms'    => 1,
                'adults'   => $room['adults'],
                'children' => $room['children'] ?? 0,
            ];
            if (!empty($room['childrenAges'])) {
                $roomData['paxes'] = array_map(
                    fn($age) => ['type' => 'CH', 'age' => $age],
                    $room['childrenAges']
                );
            }
            $rooms[] = $roomData;
        }

        if (count($hotelCodes) === 0) {
            return [
                'hotels'     => [],
                'checkIn'    => $request->check_in,
                'checkOut'   => $request->check_out,
                'total'      => 0,
                'zones'      => [],
                'facilities' => [],
            ];
        }

        $payload = [
            'stay'         => ['checkIn' => $request->check_in, 'checkOut' => $request->check_out],
            'occupancies'  => $rooms,
            'hotels'       => ['hotel' => $hotelCodes],
            'language'     => strtolower($request->language),
            'sourceMarket' => 'SA',
        ];

        if ($request->has('min_price'))      $payload['filter']['minRate'] = $request->min_price;
        if ($request->has('max_price'))      $payload['filter']['maxRate'] = $request->max_price;
        if ($request->has('accommodations')) $payload['accommodations'] = explode(',', $request->accommodations);

        if ($request->has('boards')) {
            $payload['boards'] = [
                'board' => explode(',', $request->boards),
                'included' => true
            ];
        }

        // 8. External API call (not cached — real-time availability)
        $availableHotels = Http::withOptions([
            'cert' => storage_path('certs/travelregions_sa.crt'),
            'ssl_key' => storage_path('certs/privateKey.txt'),
            'verify' => storage_path('certs/ca.crt'),
        ])->withHeaders([
            'Accept'      => 'application/json',
            'Api-key'     => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->post("{$this->baseUrl}/hotel-api/{$this->version}/hotels", $payload);

        if (!$availableHotels->successful()) {
            $error = $availableHotels['error'] ?? null;
            throw new \Exception(
                is_array($error) ? ($error['message'] ?? __('messages.catch')) : ($error ?: __('messages.catch'))
            );
        }

        if (empty($availableHotels['hotels']['hotels'])) {
            return [
                'hotels'     => [],
                'checkIn'    => $request->check_in,
                'checkOut'   => $request->check_out,
                'total'      => $availableHotels['hotels']['total'] ?? 0,
                'zones'      => [],
                'facilities' => [],
            ];
        }

        $hotelsData     = $availableHotels['hotels']['hotels'];
        $zones          = [];
        $facilityCounts = [];

        // 9. Single loop over API results
        foreach ($hotelsData as &$hotel) {
            $code = $hotel['code'];

            // Zones
            if (!isset($zones[$hotel['zoneCode']])) {
                $zones[$hotel['zoneCode']] = [
                    'code'  => $hotel['zoneCode'],
                    'name'  => $hotel['zoneName'],
                    'count' => 0,
                ];
            }
            $zones[$hotel['zoneCode']]['count']++;

            // Price - calculate minimum from actual room rates, excluding packaging rates
            $minRate = null;
            if (isset($hotel['rooms']) && is_array($hotel['rooms'])) {
                $allRates = [];
                foreach ($hotel['rooms'] as $room) {
                    if (isset($room['rates']) && is_array($room['rates'])) {
                        // Filter out packaging rates
                        $nonPackagingRates = array_filter($room['rates'], function ($rate) {
                            return !isset($rate['packaging']) || $rate['packaging'] === false;
                        });
                        foreach ($nonPackagingRates as $rate) {
                            if (isset($rate['net'])) {
                                $allRates[] = (float) $rate['net'];
                            }
                        }
                    }
                }
                $minRate = !empty($allRates) ? min($allRates) : $hotel['minRate'];
            } else {
                $minRate = $hotel['minRate'];
            }

            $minPrices         = $this->calculatePrice($minRate, $hotel['categoryName'], $hotel['currency']);
            $hotel['minRate']  = (string) round($minPrices['final_amount'], 2);
            $hotel['currency'] = $minPrices['converted_currency'];

            // O(1) lookups from cached data
            $localHotel      = $localHotelsMap[$code] ?? null;
            $image           = $firstImages[$code] ?? null;
            $hotelFacilities = $facilitiesByHotel[$code] ?? collect();

            $hotel['accommodationTypeCode'] = $localHotel['accommodation_type_code'] ?? null;
            $hotel['address']               = ['content' => $localHotel['address'] ?? null];
            $hotel['city']                  = ['content' => $localHotel['city'] ?? null];
            $hotel['images']                = [[
                'path'          => $image->path ?? null,
                'imageTypeCode' => $image->image_type_code ?? null,
            ]];

            $hotel['facilities'] = $hotelFacilities->map(fn($f) => [
                'facilityCode'      => $f->facility_code,
                'facilityGroupCode' => $f->facility_group_code,
            ])->values()->toArray();

            foreach ($hotelFacilities as $facility) {
                $facilityCounts[$facility->facility_code] =
                    ($facilityCounts[$facility->facility_code] ?? 0) + 1;
            }

            // Featured — resolved from cached map
            $hotel['featured'] = isset($featuredHotelCodes[$code]);
            $hotel['show_tag'] = !empty($featuredHotelCodes[$code]);
        }
        unset($hotel);

        // 10. Sort: show_tag > featured > rest
        usort($hotelsData, function ($a, $b) {
            $aPriority1 = $a['featured'] && $a['show_tag'];
            $bPriority1 = $b['featured'] && $b['show_tag'];
            if ($aPriority1 !== $bPriority1) return $bPriority1 ? 1 : -1;

            $aPriority2 = $a['featured'] && !$a['show_tag'];
            $bPriority2 = $b['featured'] && !$b['show_tag'];
            if ($aPriority2 !== $bPriority2) return $bPriority2 ? 1 : -1;

            return 0;
        });

        $hotelFacilitiesData = Facility::whereNotIn('name', ['1', '4', 'LGTBIQ friendly', 'LGBTQ friendly'])
                ->whereIn('name', ['24-hour reception','Minibar','Internet access','Gym','Valet parking','Restaurant','Spa centre','Outdoor swimming pool','Wheelchair-accessible'])
                ->select(['code', 'facility_group_code', 'name'])
                ->get()
                ->toArray();

        $roomFacilitiesData = Facility::whereNotIn('name', ['1', '4', 'LGTBIQ friendly', 'LGBTQ friendly'])
                ->whereIn('name', ['bathroom','Shower','TV','Safe box','Connecting rooms','Hot tub','Private pool'])
                ->select(['code', 'facility_group_code', 'name'])
                ->get()
                ->toArray();

        usort($zones, fn($a, $b) => $b['count'] <=> $a['count']);
        usort($hotelFacilitiesData, fn($a, $b) =>
            ($facilityCounts[$b['code']] ?? 0) <=> ($facilityCounts[$a['code']] ?? 0)
        );
        usort($roomFacilitiesData, fn($a, $b) =>
            ($facilityCounts[$b['code']] ?? 0) <=> ($facilityCounts[$a['code']] ?? 0)
        );

        return [
            'hotels'     => $hotelsData,
            'checkIn'    => $request->check_in,
            'checkOut'   => $request->check_out,
            'total'      => $availableHotels['hotels']['total'],
            'zones'      => array_values($zones),
            'facilities' => array_map(fn($f) => [
                'code' => $f['code'],
                'facility_group_code' => $f['facility_group_code'],
                'name' => $f['name'],
                'count' => $facilityCounts[$f['code']] ?? 0,
            ], $hotelFacilitiesData),
            'roomFacilities' => array_map(fn($f) => [
                'code' => $f['code'],
                'facility_group_code' => $f['facility_group_code'],
                'name' => $f['name'],
                'count' => $facilityCounts[$f['code']] ?? 0,
            ], $roomFacilitiesData)
        ];
    }

    /**
     * Get Hotel Details from HotelBeds API
     *
     * @param Request $request
     * @param string $hotelCode
     * @return \Illuminate\Http\Client\Response | array
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

        $response = Http::withOptions([
            'cert' => storage_path('certs/travelregions_sa.crt'),
            'ssl_key' => storage_path('certs/privateKey.txt'),
            'verify' => storage_path('certs/ca.crt'),
        ])->withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
            'Api-Mapping-Entity' => $this->apiMappingEntity,
        ])->get("{$this->contentApiUrl}/hotel-content-api/{$this->version}/hotels/{$hotelCode}/details", [
            'language' => strtoupper($language)
        ]);

        if ($response->successful()) {

            $hotel_content = $response->json()['hotel'];

            $availableHotels = Http::withOptions([
                'cert' => storage_path('certs/travelregions_sa.crt'),
                'ssl_key' => storage_path('certs/privateKey.txt'),
                'verify' => storage_path('certs/ca.crt'),
            ])->withHeaders([
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

                    $rateCurrency = $availableHotels->json()['hotels']['hotels'][0]['currency'];

                    // Use reference for the outer loop as well
                    foreach ($availabilityRooms as &$availabilityRoom) {

                        /* Remove rate with packaging: true */
                        if (isset($availabilityRoom['rates']) && is_array($availabilityRoom['rates'])) {
                            $availabilityRoom['rates'] = array_filter($availabilityRoom['rates'], function ($rate) {
                                return !isset($rate['packaging']) || $rate['packaging'] === false;
                            });
                        }

                        foreach ($availabilityRoom['rates'] as &$rate) {

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

        $response = Http::withOptions([
            'cert' => storage_path('certs/travelregions_sa.crt'),
            'ssl_key' => storage_path('certs/privateKey.txt'),
            'verify' => storage_path('certs/ca.crt'),
        ])->withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
            'Api-Mapping-Entity' => $this->apiMappingEntity,
        ])->get("{$this->contentApiUrl}/hotel-content-api/{$this->version}/types/accommodations", [
            'language' => strtoupper($language)
        ]);

        return $response->json()['accommodations'] ?? [];
    }

    /**
     * Get favorite hotels for a user
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection | array
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
     * @return array
     */
    public function hotelContentApiUsingCodes($hotelCodes, $page, $perPage, $language)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $hotels = Http::withOptions([
            'cert' => storage_path('certs/travelregions_sa.crt'),
            'ssl_key' => storage_path('certs/privateKey.txt'),
            'verify' => storage_path('certs/ca.crt'),
        ])->withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
            'Api-Mapping-Entity' => $this->apiMappingEntity,
        ])->get("{$this->contentApiUrl}/hotel-content-api/{$this->version}/hotels", [
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
        try {
            // Ensure URLs are initialized
            $this->initializeUrls();

            $apiKey = env('HOTEL_BEDS_API_KEY');
            $url = "{$this->baseUrl}/hotel-api/{$this->version}/bookings";

            $hotels = Http::withOptions([
                'cert' => storage_path('certs/travelregions_sa.crt'),
                'ssl_key' => storage_path('certs/privateKey.txt'),
                'verify' => storage_path('certs/ca.crt'),
            ])->withHeaders([
                'Accept' => 'application/json',
                'Api-key' => $apiKey,
                'X-Signature' => $this->generateSignature(),
            ])->post($url, [
                'holder' => [
                    'name' => $data['first_name'],
                    'surname' => $data['last_name']
                ],
                'rooms' => $data['rate_keys'],
                'clientReference' => $data['order'],
                'remark' => $data['remark'] ?? null,
                'tolerance' => 5,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('HotelBeds Booking Confirmation Connection Failed', [
                'error' => $e->getMessage(),
                'url' => $url ?? 'URL not set',
                'baseUrl' => $this->baseUrl ?? 'BaseURL not set',
                'order' => $data['order'] ?? 'N/A',
                'booking_id' => $data['booking_id'] ?? 'N/A'
            ]);

            return [
                'status' => false,
                'error' => 'Connection failed: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('HotelBeds Booking Confirmation Exception', [
                'error' => $e->getMessage(),
                'url' => $url ?? 'URL not set',
                'baseUrl' => $this->baseUrl ?? 'BaseURL not set',
                'order' => $data['order'] ?? 'N/A',
                'booking_id' => $data['booking_id'] ?? 'N/A'
            ]);

            return [
                'status' => false,
                'error' => 'Unexpected error: ' . $e->getMessage()
            ];
        }

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
     * Reconfirm a booking with HotelBeds API
     *
     * @param string $booking_reference
     * @return array
     *
     * @throws \Exception
     */
    public function bookingReconfirmation($booking_reference)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $hotels = Http::withOptions([
            'cert' => storage_path('certs/travelregions_sa.crt'),
            'ssl_key' => storage_path('certs/privateKey.txt'),
            'verify' => storage_path('certs/ca.crt'),
        ])->withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
        ])->get("{$this->baseUrl}/hotel-api/{$this->version}/bookings/reconfirmations", [
            'from' => 1,
            'to' => 10,
            'references' => $booking_reference
        ]);

        if ($hotels->successful()) {

            $hotelBooking = $hotels->json();

            if (isset($hotelBooking) && $hotelBooking) {
                foreach ($hotelBooking['bookings'] as $booking) {
                    if (isset($booking['hotel']) && $booking['hotel']) {
                        foreach ($booking['hotel'] as $hotel) {
                            if (isset($hotel['rooms']) && $hotel['rooms']) {
                                foreach ($hotel['rooms'] as $room) {
                                    BookingRoom::where('room_code', $room['code'])
                                        ->update([
                                            'supplier_confirmation_code' => isset($room[0]) && isset($room[0]['supplierConfirmationCode']) ? $room[0]['supplierConfirmationCode'] : null,
                                        ]);
                                }
                            }
                        }
                    }
                }
            }

            return [
                'status' => true,
                'data' => $hotelBooking
            ];
        }

        Log::error('HotelBeds Booking Reconfirmation Failed', $hotels->json());

        return [];
    }

    /**
     * Get locations and destinations
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Client\Response | array
     */
    public function getLocationsAndDestinations($request)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $locations = Http::withOptions([
            'cert' => storage_path('certs/travelregions_sa.crt'),
            'ssl_key' => storage_path('certs/privateKey.txt'),
            'verify' => storage_path('certs/ca.crt'),
        ])->withHeaders([
            'Accept' => 'application/json',
            'Api-key' => $apiKey,
            'X-Signature' => $this->generateSignature(),
            'Api-Mapping-Entity' => $this->apiMappingEntity,
        ])->get("{$this->contentApiUrl}/hotel-content-api/{$this->version}/locations/destinations");

        if ($locations->successful()) {
            return $locations->json();
        }

        return [];
    }

    public function checkRoomAvailability($room_rates)
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        $hotels = Http::withOptions([
            'cert' => storage_path('certs/travelregions_sa.crt'),
            'ssl_key' => storage_path('certs/privateKey.txt'),
            'verify' => storage_path('certs/ca.crt'),
        ])->withHeaders([
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
        // Ensure URLs are initialized
        $this->initializeUrls();

        $apiKey = env('HOTEL_BEDS_API_KEY');

        try {
            $hotels = Http::withOptions([
                'cert' => storage_path('certs/travelregions_sa.crt'),
                'ssl_key' => storage_path('certs/privateKey.txt'),
                'verify' => storage_path('certs/ca.crt'),
            ])->withHeaders([
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
        // Ensure URLs are initialized
        $this->initializeUrls();

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

                $hotels = Http::withOptions([
                    'cert' => storage_path('certs/travelregions_sa.crt'),
                    'ssl_key' => storage_path('certs/privateKey.txt'),
                    'verify' => storage_path('certs/ca.crt'),
                ])->withHeaders([
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
