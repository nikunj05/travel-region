<?php

namespace App\Interfaces;

interface AuthInterface
{
    public function login($data);

    public function register($data);

    public function logout($data);

    public function sendResetPasswordLink($data);

    public function resetPassword($data);
}
