@component('mail::message')
# Hello Admin

User **{{ $name }}** has sent you a message via the Contact Us form.

Email: <a href="mailto:{{ $email }}">{{ $email }}</a>

Message: **{!! $message !!}**


Best Regards,<br/>
{{ config('app.name') }}
@endcomponent
