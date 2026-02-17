<?php

namespace App\Console\Commands;

use App\Models\Destination;
use App\Models\Zone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchDestination extends Command
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
    protected $signature = 'app:fetch-destination';

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
     * Fetch destinations from API with retry logic
     */
    protected function fetchDestinationWithRetry(int $from, int $to, int $maxRetries = 3): ?array
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
                ])->get("{$this->baseUrl}/hotel-content-api/{$this->version}/locations/destinations", [
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

        $from = (int) $this->ask('Start from destination number', 1);
        $to = (int) $this->ask('Fetch up to destination number (max ' . self::PAGINATION_LIMIT . ' per request)', self::PAGINATION_LIMIT);
        $shouldTruncate = $this->confirm('Truncate existing destinations table?', false);

        // Validate input
        if ($from < 1) {
            $this->error('Start position must be at least 1.');
            return 1;
        }

        if ($to - $from + 1 > self::PAGINATION_LIMIT) {
            $this->error("Batch size cannot exceed " . self::PAGINATION_LIMIT . " destinations.");
            return 1;
        }

        if ($shouldTruncate && !$this->confirm('Are you sure you want to truncate all destinations?')) {
            $this->info('Truncation cancelled.');
            return 0;
        }

        if ($shouldTruncate) {
            Destination::truncate();
            $this->info('Destinations table truncated.');
        }

        $total = null;
        $currentFrom = $from;
        $currentTo = $to;
        $destinationCount = 0;
        $failedRanges = [];

        // Fetch all destinations in batches until we reach the total
        while (is_null($total) || $currentFrom <= $total) {
            $data = $this->fetchDestinationWithRetry($currentFrom, $currentTo);

            if ($data === null) {
                $this->error("Failed to fetch destinations from {$currentFrom} to {$currentTo}. Skipping this batch.");
                $failedRanges[] = "{$currentFrom}-{$currentTo}";
                $currentFrom += self::PAGINATION_LIMIT;
                $currentTo += self::PAGINATION_LIMIT;
                continue;
            }

            $total = $data['total'] ?? 0;

            if (empty($data['destinations'])) {
                $this->info('No more destinations to fetch.');
                break;
            }

            // remove if name is empty or null
            $data['destinations'] = array_filter($data['destinations'], fn($destination) => !empty($destination['name']['content'] ?? null));
            $destinationData = array_map(fn($destination) => [
                'code' => $destination['code'],
                'name' => $destination['name']['content'] ?? null,
                'country_code' => $destination['countryCode'] ?? null,
                'iso_code' => $destination['isoCode'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ], $data['destinations']);

            Destination::upsert($destinationData, ['code'], [
                'name',
                'country_code',
                'iso_code',
                'updated_at',
            ]);

            // insert zones for each destination
            $zoneData = [];
            foreach ($data['destinations'] as $destination) {
                if (!empty($destination['zones'])) {
                    foreach ($destination['zones'] as $zone) {
                        $zoneData[] = [
                            'destination_code' => $destination['code'],
                            'code' => $zone['zoneCode'],
                            'name' => $zone['name'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            Zone::truncate();
            Zone::upsert($zoneData, ['code'], [
                'destination_code',
                'name',
                'updated_at',
            ]);

            $destinationCount += count($destinationData);
            $this->info("Fetched destinations {$currentFrom}-{$currentTo} ({$destinationCount}/{$total} total)");

            $currentFrom += self::PAGINATION_LIMIT;
            $currentTo += self::PAGINATION_LIMIT;
        }

        $this->info("Successfully fetched and stored {$destinationCount} destinations.");

        if (!empty($failedRanges)) {
            $this->warn("Failed ranges: " . implode(', ', $failedRanges));
            $this->info('You can re-run the command starting from a failed range.');
        }

        return 0;
    }
}
