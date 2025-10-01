<?php

namespace App\Http\Controllers;

use App\Http\Resources\CmsResource;
use App\Interfaces\CmsInterface;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    protected $cmsRepository;

    public function __construct(CmsInterface $cmsRepository)
    {
        $this->cmsRepository = $cmsRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cmsContent = $this->cmsRepository->getPages();

        return $this->sendApiResponse(true, __('messages.cms.fetched'), [
            'content' => CmsResource::collection($cmsContent)
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $cmsContent = $this->cmsRepository->getPageBySlug($slug);

        return $this->sendApiResponse(true, __('messages.cms.fetched'), [
            'content' => new CmsResource($cmsContent)
        ]);
    }
}
