<?php

namespace App\Http\Controllers;

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
    public function index($type)
    {
        $cmsContent = $this->cmsRepository->getContentByType($type);

        return $this->sendApiResponse(true, __('messages.cms.fetched'), [
            'content' => $cmsContent
        ]);
    }
}
