<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavoriteHotelRequest;
use App\Http\Requests\SearchHotelRequest;
use App\Models\FavoriteHotel;
use App\Traits\HotelBedsTrait;
use Illuminate\Http\Request;

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
            $response = $this->checkHotelAvailability($request);

            return $this->sendApiResponse(true, __('messages.hotel.fetched'), [
                'hotels' => $response['hotels'],
                'checkIn' => $response['checkIn'],
                'total' => $response['total'],
                'checkOut' => $response['checkOut']
            ]);

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
            $response = $this->getHotelDetails($request, $hotelCode);

            if ($response->successful()) {
                return $this->sendApiResponse(true, __('messages.hotel.single_fetched'), [
                    'hotel' => $response->json()['hotel']
                ]);
            }

            return $this->sendApiResponse(false, __('messages.catch'), [
                'error' => $response->json()
            ], $response->status());

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
    * @param FavoriteHotel $favorite
    * @return \Illuminate\Http\JsonResponse
    */
    public function removeFavorite(FavoriteHotel $favorite)
    {
        if ($favorite->user_id !== request()->user()->id) {
            return $this->sendApiResponse(false, __('messages.unauthorized'), [], 403);
        }

        $favorite->delete();

        return $this->sendApiResponse(true, __('messages.hotel.favorite_removed'), []);
    }
}
