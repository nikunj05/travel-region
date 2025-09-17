<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationPreferenceRequest;
use App\Http\Requests\UserSettingRequest;
use App\Http\Resources\SettingResource;
use App\Http\Resources\UserSettingResource;
use App\Interfaces\AuthInterface;
use App\Models\NotificationPreference;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $authRepository;

    public function __construct(AuthInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

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

    /**
     * Get the user settings for the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userSettings(Request $request)
    {
        $user = $request->user();

        return $this->sendApiResponse(true, __('messages.user-settings.fetched'), [
            'user_settings' => new UserSettingResource($user),
        ]);
    }

    /**
     * Update the user settings for the authenticated user.
     *
     * @param UserSettingRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserSettings(UserSettingRequest $request)
    {
        $user = $this->authRepository->updateUserSettings($request);

        return $this->sendApiResponse(true, __('messages.user-settings.updated'), [
            'user_settings' => new UserSettingResource($user),
        ]);
    }
}
