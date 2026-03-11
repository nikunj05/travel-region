<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Notifications\Notification;

class AdminRequestPasswordReset extends RequestPasswordReset
{
    public function request(): void
    {
        $data = $this->form->getState();
        $email = $data['email'] ?? null;

        if (!$email) {
            Notification::make()
                ->title('Email Required')
                ->body('Please enter your email address.')
                ->danger()
                ->send();
            return;
        }

        // Check if the user exists and is an admin before proceeding
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            Notification::make()
                ->title('Account Not Found')
                ->body('No account found with this email address.')
                ->danger()
                ->send();
            return;
        }
        
        if (!$user->hasRole('admin')) {
            Notification::make()
                ->title('Access Denied')
                ->body('Password reset is only available for admin users.')
                ->danger()
                ->send();
            return;
        }

        // If validation passes, call parent request method to handle the actual password reset
        parent::request();
    }
}