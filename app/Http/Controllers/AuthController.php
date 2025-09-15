<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendResetPasswordRequest;
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
}
