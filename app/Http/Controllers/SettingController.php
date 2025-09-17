<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationPreferenceRequest;
use App\Http\Resources\SettingResource;
use App\Models\NotificationPreference;
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

    /**
     * Get the notification preferences for the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function notificationPreferences(Request $request)
    {
        $preferences = NotificationPreference::where('user_id', $request->user()->id)->first();

        return $this->sendApiResponse(true, __('messages.notification-preferences.fetched'), [
            'preferences' => $preferences,
        ]);
    }

    /**
     * Update the notification preferences for the authenticated user.
     *
     * @param NotificationPreferenceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNotificationPreferences(NotificationPreferenceRequest $request)
    {
        $preferences = NotificationPreference::where('user_id', $request->user()->id)->updateOrCreate([
            'user_id' => $request->user()->id,
        ], $request->validated());

        return $this->sendApiResponse(true, __('messages.notification-preferences.updated'), [
            'preferences' => $preferences,
        ]);
    }
}
