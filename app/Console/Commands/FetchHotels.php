<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use App\Models\HotelFacility;
use App\Models\HotelImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\json;

class FetchHotels extends Command
{
    protected $baseUrl = 'https://api.test.hotelbeds.com';
    protected $version = '1.0';
    protected $apiMappingEntity = 2716;
    protected const PAGINATION_LIMIT = 1000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-hotels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch hotels from HotelBeds API';

    /**
     * Generate HotelBeds API signature
     *
     * @return string
     */
    protected function generateSignature(): string
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');
        $secret = env('HOTEL_BEDS_SECRET');
        $timestamp = time();

        return hash('sha256', $apiKey . $secret . $timestamp);
    }

    /**
     * Fetch hotels from API with retry logic
     */
    protected function fetchHotelsWithRetry(int $from, int $to, int $maxRetries = 3): ?array
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');
        $retries = 0;

        while ($retries < $maxRetries) {
            try {
                $response = Http::timeout(0)->withHeaders([
                    'Accept' => 'application/json',
                    'Api-key' => $apiKey,
                    'X-Signature' => $this->generateSignature(),
                    'Api-Mapping-Entity' => $this->apiMappingEntity,
                ])->get("{$this->baseUrl}/hotel-content-api/{$this->version}/hotels", [
                    'from' => $from,
                    'to' => $to,
                ]);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    $this->error("Request failed (status: {$response->status()}). {$response->body()}");
                }

                $retries++;
                if ($retries < $maxRetries) {
                    $waitTime = min(2 ** $retries, 32); // Exponential backoff: 2, 4, 8, 16, 32 seconds
                    $this->warn("Request failed (status: {$response->status()}). Retrying in {$waitTime}s... (Attempt {$retries}/{$maxRetries})");
                    sleep($waitTime);
                }
            } catch (\Exception $e) {
                $retries++;
                if ($retries < $maxRetries) {
                    $waitTime = min(2 ** $retries, 32);
                    $this->warn("Connection error: {$e->getMessage()}. Retrying in {$waitTime}s... (Attempt {$retries}/{$maxRetries})");
                    sleep($waitTime);
                } else {
                    $this->error("Failed after {$maxRetries} attempts: {$e->getMessage()}");
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');

        if (!$apiKey) {
            $this->error('HOTEL_BEDS_API_KEY is not set in environment variables.');
            return 1;
        }

        $from = (int) $this->ask('Start from hotel number', 1);
        $to = (int) $this->ask('Fetch up to hotel number (max ' . self::PAGINATION_LIMIT . ' per request)', self::PAGINATION_LIMIT);
        $shouldTruncate = $this->confirm('Truncate existing hotels table?', false);

        // Validate input
        if ($from < 1) {
            $this->error('Start position must be at least 1.');
            return 1;
        }

        if ($to - $from + 1 > self::PAGINATION_LIMIT) {
            $this->error("Batch size cannot exceed " . self::PAGINATION_LIMIT . " hotels.");
            return 1;
        }

        if ($shouldTruncate && !$this->confirm('Are you sure you want to truncate all hotels?')) {
            $this->info('Truncation cancelled.');
            return 0;
        }

        if ($shouldTruncate) {
            Hotel::truncate();
            $this->info('Hotels table truncated.');
        }

        HotelImage::truncate();
        $this->info('Hotel images table truncated.');

        HotelFacility::truncate();
        $this->info('Hotel facilities table truncated.');

        $total = null;
        $currentFrom = $from;
        $currentTo = $to;
        $hotelCount = 0;
        $failedRanges = [];

        // Fetch all hotels in batches until we reach the total
        while (is_null($total) || $currentFrom <= $total) {
            $data = $this->fetchHotelsWithRetry($currentFrom, $currentTo);

            if ($data === null) {
                $this->error("Failed to fetch hotels from {$currentFrom} to {$currentTo}. Skipping this batch.");
                $failedRanges[] = "{$currentFrom}-{$currentTo}";
                $currentFrom += self::PAGINATION_LIMIT;
                $currentTo += self::PAGINATION_LIMIT;
                continue;
            }

            $total = $data['total'] ?? 0;

            if (empty($data['hotels'])) {
                $this->info('No more hotels to fetch.');
                break;
            }

            $hotelData = array_map(fn($hotel) => [
                'code' => $hotel['code'],
                'name' => $hotel['name']['content'] ?? null,
                'longitude' => $hotel['coordinates']['longitude'] ?? null,
                'latitude' => $hotel['coordinates']['latitude'] ?? null,
                'destination_code' => $hotel['destinationCode'] ?? null,
                'category_code' => $hotel['categoryCode'] ?? null,
                'category_group_code' => $hotel['categoryGroupCode'] ?? null,
                'accommodation_type_code' => $hotel['accommodationTypeCode'] ?? null,
                'city' => $hotel['city']['content'] ?? null,
                'address' => $hotel['address']['content'] ?? null,
                'zone_code' => $hotel['zoneCode'] ?? null,
                'chain_code' => $hotel['chainCode'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ], $data['hotels']);

            Hotel::upsert($hotelData, ['code'], [
                'name',
                'longitude',
                'latitude',
                'destination_code',
                'category_code',
                'category_group_code',
                'accommodation_type_code',
                'city',
                'address',
                'zone_code',
                'chain_code',
                'created_at',
                'updated_at',
            ]);

            $imageData = [];
            $facilityData = [];
            foreach ($data['hotels'] as $hotel) {
                if (!empty($hotel['images'])) {
                    foreach ($hotel['images'] as $image) {
                        $imageData[] = [
                            'hotel_code' => $hotel['code'],
                            'path' => $image['path'],
                            'image_type_code' => $image['imageTypeCode'] ?? null,
                            'order' => $image['order'] ?? null,
                            'visual_order' => $image['visualOrder'] ?? null,
                            'characteristic_code' => $image['characteristicCode'] ?? null,
                            'room_code' => $image['roomCode'] ?? null,
                            'room_type' => $image['roomType'] ?? null,
                        ];
                    }
                }

                if (!empty($hotel['facilities'])) {
                    foreach ($hotel['facilities'] as $facility) {
                        $facilityData[] = [
                            'hotel_code' => $hotel['code'],
                            'facility_code' => $facility['facilityCode'] ?? null,
                            'facility_group_code' => $facility['facilityGroupCode'] ?? null,
                        ];
                    }
                }
            }

            if (!empty($imageData)) {
                foreach (array_chunk($imageData, 1000) as $chunk) {
                    HotelImage::insertOrIgnore($chunk);
                }
            }

            if (!empty($facilityData)) {
                foreach (array_chunk($facilityData, 1000) as $facilityChunk) {
                    HotelFacility::insertOrIgnore($facilityChunk);
                }
            }

            $hotelCount += count($hotelData);
            $this->info("Fetched hotels {$currentFrom}-{$currentTo} ({$hotelCount}/{$total} total)");

            $currentFrom += self::PAGINATION_LIMIT;
            $currentTo += self::PAGINATION_LIMIT;
        }

        $this->info("Successfully fetched and stored {$hotelCount} hotels.");

        if (!empty($failedRanges)) {
            $this->warn("Failed ranges: " . implode(', ', $failedRanges));
            $this->info('You can re-run the command starting from a failed range.');
        }

        return 0;
    }
}
