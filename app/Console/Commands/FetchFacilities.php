<?php

namespace App\Console\Commands;

use App\Models\Facility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchFacilities extends Command
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
    protected $signature = 'app:fetch-facilities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * Fetch facilities from API with retry logic
     */
    protected function fetchFacilitiesWithRetry(int $from, int $to, int $maxRetries = 3): ?array
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');
        $retries = 0;

        while ($retries < $maxRetries) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Api-key' => $apiKey,
                    'X-Signature' => $this->generateSignature(),
                    'Api-Mapping-Entity' => $this->apiMappingEntity,
                ])->get("{$this->baseUrl}/hotel-content-api/{$this->version}/types/facilities", [
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

        $from = (int) $this->ask('Start from facility number', 1);
        $to = (int) $this->ask('Fetch up to facility number (max ' . self::PAGINATION_LIMIT . ' per request)', self::PAGINATION_LIMIT);
        $shouldTruncate = $this->confirm('Truncate existing facilities table?', false);

        // Validate input
        if ($from < 1) {
            $this->error('Start position must be at least 1.');
            return 1;
        }

        if ($to - $from + 1 > self::PAGINATION_LIMIT) {
            $this->error("Batch size cannot exceed " . self::PAGINATION_LIMIT . " facilities.");
            return 1;
        }

        if ($shouldTruncate && !$this->confirm('Are you sure you want to truncate all facilities?')) {
            $this->info('Truncation cancelled.');
            return 0;
        }

        if ($shouldTruncate) {
            Facility::truncate();
            $this->info('Facilities table truncated.');
        }

        $total = null;
        $currentFrom = $from;
        $currentTo = $to;
        $facilityCount = 0;
        $failedRanges = [];

        // Fetch all facilities in batches until we reach the total
        while (is_null($total) || $currentFrom <= $total) {
            $data = $this->fetchFacilitiesWithRetry($currentFrom, $currentTo);

            if ($data === null) {
                $this->error("Failed to fetch facilities from {$currentFrom} to {$currentTo}. Skipping this batch.");
                $failedRanges[] = "{$currentFrom}-{$currentTo}";
                $currentFrom += self::PAGINATION_LIMIT;
                $currentTo += self::PAGINATION_LIMIT;
                continue;
            }

            $total = $data['total'] ?? 0;

            if (empty($data['facilities'])) {
                $this->info('No more facilities to fetch.');
                break;
            }

            // remove if name is empty or null
            $data['facilities'] = array_filter($data['facilities'], fn($facility) => !empty($facility['description']['content'] ?? null));
            $facilityData = array_map(fn($facility) => [
                'code' => $facility['code'],
                'name' => $facility['description']['content'] ?? null,
                'language_code' => $facility['description']['languageCode'] ?? null,
                'facility_group_code' => $facility['facilityGroupCode'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ], $data['facilities']);

            Facility::upsert($facilityData, ['code'], [
                'name',
                'language_code',
                'facility_group_code',
                'updated_at',
            ]);

            $facilityCount += count($facilityData);
            $this->info("Fetched facilities {$currentFrom}-{$currentTo} ({$facilityCount}/{$total} total)");

            $currentFrom += self::PAGINATION_LIMIT;
            $currentTo += self::PAGINATION_LIMIT;
        }

        $this->info("Successfully fetched and stored {$facilityCount} facilities.");

        if (!empty($failedRanges)) {
            $this->warn("Failed ranges: " . implode(', ', $failedRanges));
            $this->info('You can re-run the command starting from a failed range.');
        }

        return 0;
    }
}
