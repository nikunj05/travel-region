@component('mail::message')
# Booking Cancellation Notification

Order: **{{ $booking->order ?? 'N/A' }}**

Booking Reference: **{{ $booking->booking_reference ?? 'N/A' }}**

Hotel Name: **{{ $booking->hotel_name ?? 'N/A' }}**

User Email: **{{ $booking->user->email ?? 'N/A' }}**

Check In: **{{ optional($booking->check_in)->format('Y-m-d') ?? 'N/A' }}**

Check Out: **{{ optional($booking->check_out)->format('Y-m-d') ?? 'N/A' }}**

Status: **{{ $booking->status ?? 'cancelled' }}**

Best Regards,<br/>
{{ config('app.name') }}
@endcomponent
