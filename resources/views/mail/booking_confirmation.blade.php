@component('mail::message')
# {{ __('messages.booking_confirmation.title') }}

{{ __('messages.booking_confirmation.greeting') }} {{ $booking->primary_details->first_name . ' ' . $booking->primary_details->last_name }},

{{ __('messages.booking_confirmation.thank_you') }}

## **{{ __('messages.booking_confirmation.booking_details') }}**

<table style="width: 100%; border-collapse: collapse; text-align: center;">
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.hotel_name') }}
        </th>
         <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->hotel_name }}
        </td>
    </tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.check_in') }}
        </th>
         <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->check_in->format('D, d M') }}
        </td>
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.check_out') }}
        </th>
         <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->check_out->format('D, d M') }}
        </td>
    </tr>
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.guests') }}
        </th>
         <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->adults + $booking->children }} (
            {{ $booking->adults }} {{ trans_choice('messages.booking_confirmation.adult', $booking->adults) }},
            {{ $booking->children }} {{ trans_choice('messages.booking_confirmation.child', $booking->children) }}
            )
        </td>
    </tr>
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.rooms') }}
        </th>
        <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->rooms }}
        </td>
    </tr>
    
</table>

<br />

<br />
<p>{{ __('messages.booking_confirmation.footer') }}</p>
<br />
<p>{{ __('messages.booking_confirmation.regards') }}<br><b>{{ config('app.name') }}</b></p>
@endcomponent