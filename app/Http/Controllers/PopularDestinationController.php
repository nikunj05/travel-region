<?php

namespace App\Http\Controllers;

use App\Http\Resources\PopularDestinationResource;
use App\Models\PopularDestination;
use Illuminate\Http\Request;

class PopularDestinationController extends Controller
{
    /**
     * Display a listing of the FAQs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $destinations = PopularDestination::latest()->get();

        return $this->sendApiResponse(true, __('messages.popular_destinations.fetched'), [
            'destinations' => PopularDestinationResource::collection($destinations),
        ]);
    }
}
