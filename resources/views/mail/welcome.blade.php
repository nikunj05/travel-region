@component('mail::message')

Hello {{ $user->first_name . ' ' . $user->last_name }},

<p>Thank you for registering with {{ config('app.name') }}. We are excited to have you on board!</p>

<p>We hope you have a great experience using our services. If you have any questions or need assistance, feel free to reach out to our support team.</p>

<p>{{ __('messages.booking_confirmation.regards') }}<br><b>{{ config('app.name') }}</b></p>

@endcomponent
