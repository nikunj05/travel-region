<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Interfaces\AuthInterface;

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
}
