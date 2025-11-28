<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GooglePlacesController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (empty($query) || strlen($query) < 3) {
            return response()->json(['results' => []]);
        }

        $apiKey = env('GOOGLE_PLACES_API_KEY');

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                'types' => 'regions', // Restrict to cities only
                'language' => 'en',
                'query' => $query,
                'key' => $apiKey,
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch places'], 500);
        }
    }
}
