<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Handle the incoming request to get settings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $setting = Setting::first();

        return $this->sendApiResponse(true, __('messages.blog.fetched'), [
            'setting' => new SettingResource($setting),
        ]);
    }
}
