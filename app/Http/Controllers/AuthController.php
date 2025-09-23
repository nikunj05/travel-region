<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendResetPasswordRequest;
use App\Http\Requests\SocialMediaRequest;
use App\Http\Resources\UserResource;
use App\Interfaces\AuthInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Handle an authentication attempt.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $userData = $this->authRepository->login($request);

        return $this->sendApiResponse(true, __('messages.login'), [
            'user' => new UserResource($userData['user']),
            'token' => $userData['token'],
        ], 200);
    }

    /**
     * Handle logout flow.
     *
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->authRepository->logout($request);

        return $this->sendApiResponse(true, __('messages.logout'), [], 200);
    }

    /**
     * Handle registration.
     *
     * @param  RegisterRequest  $request  The incoming registration request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $user = $this->authRepository->register($request);

        return $this->sendApiResponse(
            true,
            __('messages.register'),
            ['user' => new UserResource($user)],
            201
        );
    }

    /**
     * Handle social authentication.
     *
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function socialAuth(SocialMediaRequest $request)
    {
        $userData = $this->authRepository->socialAuth($request);

        return $this->sendApiResponse(true, __('messages.login'), [
            'user' => new UserResource($userData['user']),
            'token' => $userData['token'],
        ], 200);
    }

    /**
     * Handle send reset password link.
     *
     * @param  SendResetPasswordRequest  $request  indicate send reset password link to user mail.
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(SendResetPasswordRequest $request)
    {
        $resetUrl = $this->authRepository->sendResetPasswordLink($request);

        return $this->sendApiResponse(
            true,
            __('messages.reset_password'),
            ['reset_link' => $resetUrl],
            200
        );
    }

    /**
     * Handle reset password.
     *
     * @param  ResetPasswordRequest  $request  indicate password reset successfully flow.
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authRepository->resetPassword($request);

        return $this->sendApiResponse(true, __('messages.reset_password_msg'), [], 200);
    }

    /**
     * Handle change password.
     *
     * @param  ChangePasswordRequest  $request  indicate password change successfully flow.
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $this->authRepository->changePassword($request);

        return $this->sendApiResponse(true, __('messages.password_change'), [], 200);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param  Request  $request  The incoming request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        return $this->sendApiResponse(true, '', [
            'user' => new UserResource($request->user()),
        ], 200);
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  ProfileRequest  $request  The incoming request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(ProfileRequest $request)
    {
        $user = $this->authRepository->updateProfile($request);

        return $this->sendApiResponse(true, '', [
            'user' => new UserResource($user),
        ], 200);
    }
}
