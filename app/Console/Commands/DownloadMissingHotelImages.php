<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use App\Models\HotelImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DownloadMissingHotelImages extends Command
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
    protected $signature = 'app:download-missing-hotel-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hotelCodes = Hotel::whereDoesntHave('images')->where('status', 1)->pluck('code')->toArray(); // Fix #1

        $this->info("Found " . count($hotelCodes) . " hotels with missing images.");

        foreach (array_chunk($hotelCodes, 100) as $hotelChunk) { // renamed to $hotelChunk
            $data = $this->fetchHotelsWithRetry($hotelChunk);

            if (!$data) {
                $this->error("Failed to fetch data for hotel (Code: " . implode(', ', $hotelChunk) . "). Skipping...");
                continue;
            }

            $imageData = [];
            foreach ($data['hotels'] as $hotel) {
                if (!empty($hotel['images'])) {
                    foreach ($hotel['images'] as $image) {
                        $imageData[] = [
                            'hotel_code'         => $hotel['code'],
                            'path'               => $image['path'],
                            'image_type_code'    => $image['imageTypeCode'] ?? null,
                            'order'              => $image['order'] ?? null,
                            'visual_order'       => $image['visualOrder'] ?? null,
                            'characteristic_code'=> $image['characteristicCode'] ?? null,
                            'room_code'          => $image['roomCode'] ?? null,
                            'room_type'          => $image['roomType'] ?? null,
                        ];
                    }
                }
            }

            if (!empty($imageData)) {
                $this->info("Saving " . count($imageData) . " images for hotel (Code: " . implode(', ', $hotelChunk) . ")...");
                foreach (array_chunk($imageData, 1000) as $imageChunk) { // renamed to $imageChunk
                    $this->info("Inserting " . count($imageChunk) . " images...");
                    HotelImage::insertOrIgnore($imageChunk);
                }
            } else {
                $this->warn("No images found in API response for hotel (Code: " . implode(', ', $hotelChunk) . ")");
            }
        }
    }

    protected function fetchHotelsWithRetry($hotelCodes, int $maxRetries = 3): ?array
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
                    'codes' => implode(',', $hotelCodes)
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

    protected function generateSignature(): string
    {
        $apiKey = env('HOTEL_BEDS_API_KEY');
        $secret = env('HOTEL_BEDS_SECRET');
        $timestamp = time();

        return hash('sha256', $apiKey . $secret . $timestamp);
    }
}
