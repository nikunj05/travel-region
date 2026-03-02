<?php

namespace App\Http\Controllers;

use App\Exports\HotelsExport;
use App\Http\Requests\FavoriteHotelRequest;
use App\Http\Requests\SearchHotelRequest;
use App\Models\Board;
use App\Models\Destination;
use App\Models\FavoriteHotel;
use App\Models\Hotel;
use App\Traits\HotelBedsTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HotelController extends Controller
{
    use HotelBedsTrait;

    /**
     * Handle the incoming request to search for hotels.
     *
     * @param SearchHotelRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(SearchHotelRequest $request)
    {
        try {
            $response = $this->checkHotelAvailabilityNew($request);

            return $this->sendApiResponse(true, __('messages.hotel.fetched'), $response);

        } catch (\Exception $e) {
            return $this->sendApiResponse(false, __('messages.catch'), [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get accommodation types from HotelBeds API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function accommodationTypes()
    {
        $response = $this->getAccommodationTypes();

        return $this->sendApiResponse(true, __('messages.accommodation_types_fetched'), [
            'accommodation_types' => $response
        ]);
    }

    /**
     * Get details of a specific hotel by its code.
     *
     * @param string $hotelCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $hotelCode)
    {
        try {
            $hotels = $this->getHotelDetails($request, $hotelCode);

            return $this->sendApiResponse(true, __('messages.hotel.single_fetched'), [
                'hotel' => $hotels['hotel'],
                'checkIn' => $hotels['checkIn'],
                'checkOut' => $hotels['checkOut'],
                'rooms' => $hotels['rooms'],
            ]);
        } catch (\Exception $e) {
            return $this->sendApiResponse(false, __('messages.catch'), [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List favorite hotels of the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listFavorites(Request $request)
    {
        $favorites = $this->getFavoriteHotels($request, $request->user());

        return $this->sendApiResponse(true, __('messages.hotel.favorites_fetched'), [
            'favorites' => $favorites
        ]);
    }

    /**
    * Add a hotel to the authenticated user's favorites.
    *
    * @param FavoriteHotelRequest $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function addFavorite(FavoriteHotelRequest $request)
    {
        $favorite = FavoriteHotel::create([
            'user_id' => $request->user()->id,
            'hotel_codes' => $request->hotel_code,
        ]);

        return $this->sendApiResponse(true, __('messages.hotel.favorite_added'), [
            'favorite' => $favorite
        ]);
    }

    /**
    * Remove a hotel from the authenticated user's favorites.
    *
    * @param string $hotelCode
    * @return \Illuminate\Http\JsonResponse
    */
    public function removeFavorite($hotelCode)
    {
        FavoriteHotel::where('hotel_codes', $hotelCode)->where('user_id', auth()->id())->delete();

        return $this->sendApiResponse(true, __('messages.hotel.favorite_removed'), []);
    }

    /**
     * Get locations and destinations from HotelBeds API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function locationsDestinations(Request $request)
    {
        $destinations = Destination::query();

        if ($request->has('search')) {
            $destinations = $destinations->where('name', 'like', '%' . $request->search . '%');
        }

        $destinations = $destinations
            ->limit(10)
            ->get();

        $hotels = Hotel::where('status', 1);

        if ($request->has('search')) {
            $hotels = $hotels->where('name', 'like', '%' . $request->search . '%');
        }

        $hotels = $hotels
            ->limit(10)
            ->get();

        return $this->sendApiResponse(true, __('messages.locations_destinations_fetched'), [
            'destinations' => $destinations,
            'hotels' => $hotels
        ]);
    }

    /**
     * Get board types from HotelBeds API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function boards()
    {
        $boards = Board::whereIn('name', ['BED AND BREAKFAST', 'FULL BOARD', 'HALF BOARD', 'ROOM ONLY'])->get();

        return $this->sendApiResponse(true, __('messages.board_types_fetched'), [
            'board_types' => $boards
        ]);
    }

    /**
     * Export all hotels to Excel file and store in public folder.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportExcel()
    {
        try {
            // Check if hotels exist
            $totalHotels = Hotel::count();
            if ($totalHotels === 0) {
                return $this->sendApiResponse(false, 'No hotels found to export', []);
            }

            // Generate unique filename with timestamp
            $fileName = 'hotels_export_' . date('Y_m_d_His') . '.xlsx';
            $filePath = 'exports/' . $fileName;
            
            // Store in storage/app/public using Laravel's public disk
            $result = Excel::store(new HotelsExport, $filePath, 'public');
            
            if (!$result) {
                return $this->sendApiResponse(false, 'Excel store operation failed', [], 500);
            }

            // Get the full path for verification
            $fullPath = storage_path('app/public/' . $filePath);
            
            // Check if file was created successfully
            if (!file_exists($fullPath)) {
                return $this->sendApiResponse(false, 'Failed to create Excel file', [
                    'expected_path' => $fullPath,
                    'storage_path' => storage_path('app/public'),
                    'exports_dir_exists' => is_dir(storage_path('app/public/exports'))
                ], 500);
            }

            $fileSize = filesize($fullPath);
            
            // Generate the public URL (assumes storage link is created)
            $downloadUrl = asset('storage/' . $filePath);

            return $this->sendApiResponse(true, 'Hotels exported successfully', [
                'file_name' => $fileName,
                'file_path' => $filePath,
                'download_url' => $downloadUrl,
                'file_size' => $this->formatBytes($fileSize),
                'total_hotels' => $totalHotels,
                'storage_path' => $fullPath
            ]);

        } catch (\Exception $e) {
            return $this->sendApiResponse(false, 'Export failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Format bytes to human readable format.
     *
     * @param int $size
     * @param int $precision
     * @return string
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }
}
