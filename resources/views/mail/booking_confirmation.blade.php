@component('mail::message')
# {{ __('messages.booking_confirmation.title', [], $language) }}

{{ __('messages.booking_confirmation.greeting', [], $language) }} {{ $booking->primary_details->first_name . ' ' . $booking->primary_details->last_name }},

{{ __('messages.booking_confirmation.thank_you', [], $language) }}

## **{{ __('messages.booking_confirmation.booking_details', [], $language) }}**

<table style="width: 100%; border-collapse: collapse; text-align: center;">
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.hotel_name', [], $language) }}
        </th>
        <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->hotel_name }}
        </td>
    </tr>
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.check_in', [], $language) }}
        </th>
        <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->check_in->format('D, d M') }}
        </td>
    </tr>
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.check_out', [], $language) }}
        </th>
        <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->check_out->format('D, d M') }}
        </td>
    </tr>
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.guests', [], $language) }}
        </th>
        <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->adults + $booking->children }} (
            {{ $booking->adults }} {{ __('messages.booking_confirmation.adult', [], $language) }},
            {{ $booking->children }} {{ __('messages.booking_confirmation.child', [], $language) }}
            )
        </td>
    </tr>
    <tr>
        <th style="border: 1px solid #ddd; padding: 10px;">
            {{ __('messages.booking_confirmation.rooms', [], $language) }}
        </th>
        <td style="border: 1px solid #ddd; padding: 10px;">
            {{ $booking->rooms }}
        </td>
    </tr>

</table>

<br />

<br />
<p>{{ __('messages.booking_confirmation.footer', [], $language) }}</p>
<br />
<p>{{ __('messages.booking_confirmation.regards', [], $language) }}<br><b>{{ config('app.name') }}</b></p>
@endcomponent
