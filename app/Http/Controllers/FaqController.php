<?php

namespace App\Http\Controllers;

use App\Http\Resources\FaqCategoryResource;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the FAQs.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $faqs = FaqCategory::with('faqs')->whereHas('faqs')->get();

        return $this->sendApiResponse(true, __('messages.faq.fetched'), [
            'faqs' => FaqCategoryResource::collection($faqs),
        ]);
    }
}
