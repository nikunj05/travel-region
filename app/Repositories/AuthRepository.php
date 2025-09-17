<?php

namespace App\Repositories;

use App\Interfaces\AuthInterface;
use App\Jobs\SendResetPasswordMail;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthRepository implements AuthInterface
{
    public function login($request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw new AuthenticationException(__('messages.invalid_credentials'));
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Registers a new user by validating input and storing the user data.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request object.
     * @return \App\Models\User The created user.
     */
    public function register($data)
    {
        $validated = $data->validated();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'country_code' => $validated['country_code'],
            'mobile' => $validated['mobile'],
        ]);

        $user->assignRole('customer');

        return $user;
    }

    /**
     * Logs out the authenticated user by deleting their tokens.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request object containing the authenticated user.
     * @return void
     */
    public function logout($request)
    {
        return $request->user()->currentAccessToken()->delete();
    }

    /**
     * Sends a password reset link to the user's email.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request object containing the user's email.
     * @return string The URL of the reset password page.
     */
    public function sendResetPasswordLink($request)
    {
        $user = User::where('email', $request->email)->first();

        $token = Str::random(60);

        PasswordReset::updateOrCreate([
            'email' => $user->email
        ], [
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        $resetUrl = env('FRONTEND_URL')."reset-password/$token?email=$user->email";

        dispatch(new SendResetPasswordMail($user, $resetUrl));

        return $resetUrl;
    }

    /**
     * Resets the user's password after validating the token.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request object containing email, token, and new password.
     * @return void
     *
     * @throws AuthenticationException If the token is invalid or expired.
     */
    public function resetPassword($request)
    {
        $resetRequest = PasswordReset::where('email', $request->email)->first();

        if (! $resetRequest || ! Hash::check($request->token, $resetRequest->token)) {
            throw new AuthenticationException(__('messages.invalid_or_expired_token'));
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        return PasswordReset::where('email', $request->email)->delete();
    }

    /**
     * Changes the authenticated user's password after verifying the old password.
     *
     * @param  \Illuminate\Http\Request  $request  The request object containing the old and new passwords.
     * @return bool True if the password is successfully changed.
     *
     * @throws AuthenticationException If the old password is incorrect.
     */
    public function changePassword($request)
    {
        $user = User::find(Auth::id());

        if (! Hash::check($request->old_password, $user->password)) {
            throw new AuthenticationException(__('messages.password_incorrect'));
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return true;
    }

    /**
     * Updates the authenticated user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request object containing profile data.
     * @return \App\Models\User The updated user.
     */
    public function updateProfile($request)
    {
        $user = User::find(Auth::id());

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        // $user->email = $request->email;
        $user->country_code = $request->country_code;
        $user->mobile = $request->mobile;
        $user->gender = $request->gender;
        $user->date_of_birth = $request->date_of_birth;
        $user->nationality = $request->nationality;
        $user->address = $request->address;
        $user->passport_number = $request->passport_number;
        $user->save();

        return $user;
    }

    /**
     * Updates the authenticated user's settings.
     *
     * @param  \Illuminate\Http\Request  $request  The incoming request object containing settings data.
     * @return \App\Models\User The updated user.
     */
    public function updateUserSettings($request)
    {
        $user = User::find(Auth::id());

        $user->language = $request->language;
        $user->currency = $request->currency;
        $user->email = $request->email;
        $user->country_code = $request->country_code;
        $user->mobile = $request->mobile;
        $user->save();

        if ($request->password) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return $user;
    }
}
