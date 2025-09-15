@component('mail::message')
# Hello, {{ $user->first_name }} {{ $user->last_name }}

Click the button below to reset your password:

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

If you did not request this, please ignore this email.

Best Regards,<br/>
{{ config('app.name') }}
@endcomponent
