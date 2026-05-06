@component('mail::message')
# Booking Cancellation Notification

A booking has been cancelled and requires manual refund processing by admin.

Order: **{{ $booking->order ?? 'N/A' }}**

Booking Reference: **{{ $booking->booking_reference ?? 'N/A' }}**

Hotel Name: **{{ $booking->hotel_name ?? 'N/A' }}**

User Email: **{{ $booking->user->email ?? 'N/A' }}**

Check In: **{{ optional($booking->check_in)->format('Y-m-d') ?? 'N/A' }}**

Check Out: **{{ optional($booking->check_out)->format('Y-m-d') ?? 'N/A' }}**

Status: **{{ $booking->status ?? 'cancelled' }}**

No automatic refund amount has been applied in the system.

Best Regards,<br/>
{{ config('app.name') }}
@endcomponent