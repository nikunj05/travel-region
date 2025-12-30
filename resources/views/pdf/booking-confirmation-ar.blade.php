<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Invoice</title>

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif !important;
            margin: 0;
            padding: 20px;
        }
    </style>
</head>

<body style="margin:0;padding:0;box-sizing:border-box;">
    <div style="display:flex; justify-content:center; padding:0px 0; font-family:Poppins, sans-serif;">
        <div style="width:100%; max-width:95%; background:white; padding:15px; "> <!-- width:794px -->

            <!-- HEADER -->
            <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 16px;">
                <tr>
                    <td style="width: 35%; vertical-align: top;">
                        <img src="{{ public_path('images/logo.png') }}" width="145" style="margin:0; padding:0;">
                    </td>
                    <td style="width: 25%; text-align: center; vertical-align: top;">
                        <div style="font-size:22px; font-weight:700; color:#0b343a;">
                            Booking Voucher
                        </div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: top;">
                        <div style="color:#0b343a; font-size:13px;">
                            Booking Id: <strong>{{ $booking->order }}</strong>
                        </div>
                        <div style="color:#0b343a; font-size:13px;">
                            Hotel Beds Reference: <strong>{{ $booking->booking_reference }}</strong>
                        </div>
                        <div style="margin-top:6px; color:#0b343a; font-size:12px;">
                            (Booked on {{ $booking->created_at->format('d M Y, h:i A') }})
                        </div>
                    </td>
                </tr>
            </table>
            <main style="color: #0b343a;">
                <div style="border: 1px solid #dbc8b6; border-radius: 6px;">
                    <table width="100%" cellspacing="0" cellpadding="0"
                        style="padding:24px 15px 0; border-bottom:1px solid #dbc8b6;">
                        <tr>
                            <!-- LEFT CONTENT -->
                            <td valign="top" style="width:70%; padding-right:24px;">

                                <div style="font-size:22px; font-weight:700; margin-bottom:6px;">
                                    {{ $booking->hotel_name }}
                                    <span style="">
                                        @php
                                            $start_count = 0;
                                            if ($booking->category) {
                                                $start_count = str_replace([' STARS', ' STAR'], '', $booking->category);
                                            }
                                        @endphp
                                        @for ($i = 0; $i < $start_count; $i++)
                                            <img src="{{ public_path('images/star.svg') }}" alt="star" width="16"
                                                height="16" style="object-fit: cover;">
                                        @endfor
                                    </span>
                                </div>

                                <!-- ADDRESS -->
                                <div style="margin-bottom:24px;">
                                    <p style="color:#0b343a; font-size:14px; margin:0 0 16px 0;">
                                        {{ $booking->address }}
                                    </p>
                                </div>

                                <!-- Accommodation Type -->
                                <div style="margin-bottom:8px;">
                                    <p style="color:#0b343a; font-size:14px; margin:0 0 4px 0;">
                                        Accommodation Type: {{ $booking->accommodation_type }}
                                    </p>
                                </div>

                                @foreach (json_decode($booking->phone) as $phone)
                                    @php
                                        $type = 'Phone';
                                        if ($phone->phoneType == 'PHONEBOOKING') {
                                            $type = 'Booking Phone';
                                        } elseif ($phone->phoneType == 'PHONEHOTEL') {
                                            $type = 'Hotel Phone';
                                        } elseif ($phone->phoneType == 'PHONEMANAGEMENT') {
                                            $type = 'Management Phone';
                                        } elseif ($phone->phoneType == 'PHONEHOTEL') {
                                            $type = 'Hotel Phone';
                                        } elseif ($phone->phoneType == 'FAXNUMBER') {
                                            $type = 'Fax Number';
                                        }
                                    @endphp
                                    <div style="margin-bottom:8px;">
                                        <p style="color:#0b343a; font-size:14px; margin:0 0 4px 0;">
                                            {{ $type }}: {{ $phone->phoneNumber }}
                                        </p>
                                    </div>
                                @endforeach
                            </td>

                            <!-- RIGHT SVG -->
                            <td valign="top" align="right" style="width:30%;">
                                <img src="{{ public_path('images/thank-you-icon.png') }}" alt="decor image"
                                    width="110" height="110"
                                    style="object-fit: cover; rotate: 20deg; transform: rotate(20deg);">

                            </td>
                        </tr>
                    </table>

                    <div style="padding: 0px 15px;">
                        <table width="100%" cellspacing="0" cellpadding="0"
                            style="border-collapse:collapse; table-layout:fixed; padding:15px 0;">

                            <!-- ROW 1 — Nights / Check-in / Check-out -->
                            <tr style="border-bottom:1px solid #dbc8b6;">

                                <!-- Nights Stay -->
                                <td valign="top" style="padding:15px 10px;">
                                    <table cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td valign="middle">
                                                <img src="{{ public_path('images/calendar.svg') }}" width="20"
                                                    height="20">
                                            </td>
                                            <td valign="middle" style="padding-left:6px;">
                                                <span style="font-weight:700; font-size:15px;">{{ $booking->nights }}
                                                    Nights
                                                    Stay</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <!-- Check-in -->
                                <td valign="top" style="padding:15px 10px;">
                                    <div style="font-weight:700; font-size:14px; margin-bottom:6px;">Check-in</div>
                                    <div style="font-size:16px; font-weight:700;">
                                        {{ $booking->check_in->format('D, d M') }}
                                        <span style="font-size:14px; font-weight:400;">
                                            {{ $booking->check_in->format('Y') }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Check-out -->
                                <td valign="top" style="padding:15px 10px;">
                                    <div style="font-weight:700; font-size:14px; margin-bottom:6px;">Check-out</div>

                                    <div style="font-size:16px; font-weight:700;">
                                        <span style="font-weight:400;">
                                            {{ $booking->check_out->format('D') }},
                                        </span>
                                        {{ $booking->check_out->format('d M') }}
                                        <span style="font-size:14px; font-weight:400;">
                                            {{ $booking->check_out->format('Y') }}
                                        </span>
                                    </div>

                                </td>
                            </tr>

                            <!-- ROW 2 — Guests + Primary Guest -->
                            <tr style="border-bottom:1px solid #dbc8b6;">

                                <!-- Guests -->
                                <td valign="top" style="padding:15px 10px;">
                                    <table cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td valign="top">
                                                <img src="{{ public_path('images/user.svg') }}" width="20"
                                                    height="20">
                                            </td>
                                            <td valign="top" style="padding-left:10px;">
                                                <div style="font-weight:700; font-size:15px;">
                                                    {{ $booking->adults + $booking->children }} Guests</div>
                                                <div style="font-size:13px; margin-top:4px;">
                                                    ({{ $booking->adults }} Adults & {{ $booking->children }}
                                                    Children)
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <!-- Primary Guest -->
                                <td colspan="2" valign="top" style="padding:15px 10px;">
                                    <div style="font-size:15px; font-weight:700; margin-bottom:6px;">
                                        {{ $booking->primary_details->first_name . ' ' . $booking->primary_details->last_name }}
                                        <span style="font-size:14px; font-weight:400; margin-left:5px;">(Primary
                                            Guest)</span>
                                    </div>

                                    <div style="margin-top:16px;">
                                        <a href="mailto:{{ $booking->primary_details->email }}"
                                            style="color:#0B343A; font-size:14px; text-decoration:none;">
                                            {{ $booking->primary_details->email }}
                                        </a>,
                                        <span style="font-size:14px;">
                                            {{ $booking->primary_details->country_code . $booking->primary_details->phone }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- ROW 3 — Room Details -->
                            <tr style="border-bottom:1px solid #dbc8b6;">
                                <td valign="top" style="padding:15px 10px;">
                                    <table>
                                        <tr>
                                            <td valign="top">
                                                <img src="{{ public_path('images/door.svg') }}" width="20"
                                                    height="20">
                                            </td>
                                            <td valign="top" style="padding-left:10px;">
                                                <span style="font-weight:700; font-size:17px;">
                                                    {{ $booking->rooms }} Rooms
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td colspan="2" valign="top" style="padding:15px 10px 0px;">
                                    <div style="font-size:16px; font-weight:700; margin-bottom:8px;">
                                        {{-- Premier Deluxe Room --}}
                                    </div>
                                </td>
                            </tr>

                        </table>
                        <table width="100%" cellspacing="0" cellpadding="0"
                            style="border-collapse: collapse;
                                table-layout: fixed;
                                width: 100%;
                                max-width: 100%;
                                margin: 0;
                                padding: 0;">

                            <tr style="border-bottom:1px solid #dbc8b6;">
                                <td valign="top" style="padding:12px 10px;">
                                    <span style="font-size:15px; font-weight:700; margin-left:0px;">Sub Total</span>
                                </td>

                                <td></td>

                                <td valign="middle" align="right" style="padding:12px 10px;">
                                    <span style="font-size:16px; font-weight:700;">
                                        {{ $booking->currency }}
                                        {{ $booking->total_price }}
                                    </span>
                                </td>
                            </tr>

                            <!-- ROW: SECURITY AMOUNT -->
                            <tr style="border-bottom:1px solid #dbc8b6;">
                                <td valign="top" style="padding:12px 10px;">
                                    <span style="font-size:15px; font-weight:700; margin-left:0px;">Discount</span>
                                </td>

                                <td></td>

                                <td valign="middle" align="right" style="padding:12px 10px;">
                                    <span style="font-size:16px; font-weight:700;">
                                        {{ $booking->currency }} {{ $booking->discount_amount }}
                                    </span>
                                </td>
                            </tr>

                            <!-- FINAL AMOUNT ROW -->
                            <tr style="background:#f6fffb; border-top:2px solid #156874; margin-bottom:12px;">
                                <td valign="middle" style="padding:14px 10px;">
                                    <span style="font-size:17px; font-weight:700; color:#156874;">
                                        Total Amount
                                    </span>
                                </td>

                                <td></td>

                                <td valign="middle" align="right" style="padding:14px 10px;">
                                    <span style="font-size:19px; font-weight:700; color:#156874;">
                                        {{ $booking->currency }}
                                        {{ $booking->total_price - $booking->discount_amount }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>

                <div>
                    <h4 style="font-size: 18px; font-weight: 700; margin: 15px 0 15px;">Rooms</h4>

                    @foreach ($booking->booking_room as $booking_room)
                        <table width="100%" cellspacing="0" cellpadding="0"
                            style="border:1px solid #dbc8b6; border-radius:0px; margin-top:24px; overflow:hidden;">
                            <tr>
                                <td style="padding:15px 15px 24px; ">

                                    <table width="100%" cellspacing="0" cellpadding="0"
                                        style="border-collapse:collapse;">
                                        <tr>

                                            <!-- LEFT CONTENT -->
                                            <td valign="top" style="padding-right:12px;">
                                                <h4 style="font-size:16px; font-weight:700; margin:9px 0 15px;">
                                                    {{ $booking_room->room_name }}
                                                </h4>

                                                <table cellspacing="0" cellpadding="0"
                                                    style="border-collapse:collapse;">

                                                    <!-- Row 1 -->
                                                    <tr>
                                                        <td valign="middle"
                                                            style="vertical-align:middle; font-size:14px;">
                                                            {{ $booking_room->board_name }}
                                                        </td>
                                                    </tr>

                                                    <!-- Row 2 -->
                                                    @if ($booking_room->cancellation_policies->count() > 0)
                                                        <tr>
                                                            <td valign="middle"
                                                                style="vertical-align:middle; font-size:14px; padding-top:8px;">
                                                                <span style="font-weight:700;">
                                                                    Cancellation Policy
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @foreach ($booking_room->cancellation_policies as $cancellation_policy)
                                                            <tr>
                                                                <td valign="middle"
                                                                    style="vertical-align:middle; font-size:14px; padding-top:8px;">
                                                                    {{ $cancellation_policy->amount }} After {{ \Carbon\Carbon::parse($cancellation_policy->from)->format('d M Y h:i A') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </table>
                                            </td>

                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>
                    @endforeach
                </div>

                <!-- FOOTER -->
                <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:30px;">
                    <tr>
                        <td>
                            <table cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                <tr>
                                    <td valign="middle" style="padding-bottom:12px;">
                                        <span style="font-size:24px; font-weight:700; color:#f6be00;">
                                            Travel Region Support
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0;">
                            <table cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                <tr>
                                    <td valign="middle" style="padding:0; color:#156874;">
                                        <a href="mailto:info@travelregions.sa"
                                            style="color:#156874; font-size:16px; text-decoration:none;">
                                            info@travelregions.sa</a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
            </main>
        </div>
    </div>
</body>

</html>
