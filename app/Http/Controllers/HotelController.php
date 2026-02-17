<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavoriteHotelRequest;
use App\Http\Requests\SearchHotelRequest;
use App\Models\Destination;
use App\Models\FavoriteHotel;
use App\Models\Hotel;
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
}
