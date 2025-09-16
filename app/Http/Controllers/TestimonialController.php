<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaginationResource;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    /**
     * Handle the incoming request to search for testimonials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $testimonials = Testimonial::latest()->paginate();

        return $this->sendApiResponse(true, __('messages.testimonial.fetched'), [
            'testimonials' => TestimonialResource::collection($testimonials),
            'pagination' => new PaginationResource($testimonials)
        ]);
    }

    /**
     * Get details of a specific testimonial by its code.
     *
     * @param Testimonial $testimonial
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Testimonial $testimonial)
    {
        return $this->sendApiResponse(true, __('messages.testimonial.single_fetched'), [
            'testimonial' => new TestimonialResource($testimonial)
        ]);
    }
}
