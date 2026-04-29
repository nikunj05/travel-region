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

        @page {
            margin: 40px 20px; /* top | sides | bottom */
        }

        /* Footer positioned at the bottom of each page */
        #pdf-footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
    </style>
</head>

<body style="margin:0;padding:0;box-sizing:border-box;">
    <div id="pdf-footer">
        Payable through <strong>{{ $booking->supplier_name }}</strong>, acting as agent for the service operating company, details of which can be provided upon request. VAT: <strong>{{ $booking->vat_number }}</strong> Reference: <strong>{{ $booking->booking_reference }}</strong>
    </div>
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
                            Hotel Beds Reference: <strong style="color:#445d94;">{{ $booking->booking_reference }}</strong>
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

                                @if ($booking->phone)
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
                                @endif
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
                                                    @if($booking->child_age)
                                                        <br>
                                                        <strong>Child Ages:</strong>
                                                        @foreach(json_decode($booking->child_age) as $age)
                                                            {{ $age }}@if(!$loop->last), @endif
                                                        @endforeach
                                                    @endif
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
                            <tr>
                                <td valign="top" style="padding:15px 10px;" colspan="3">
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
                            </tr>

                        </table>
                    </div>

                </div>

                <div>
                    <h4 style="font-size: 18px; font-weight: 700; margin: 15px 0 8px 0;">Rooms</h4>

                    @foreach ($booking->booking_room as $index => $booking_room)
                        <div style="border: 1px solid #dbc8b6; border-radius: 6px; margin-bottom: 24px; padding:0;">
                        <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                            <tr>
                                <td style="padding:15px 15px 24px; ">

                                    <table width="100%" cellspacing="0" cellpadding="0"
                                        style="border-collapse:collapse;">
                                        <tr>
                                            <td valign="top" style="padding-right:12px; width: 50%;" colspan="2">
                                                <span style="font-size:16px; font-weight:700; margin:9px 0 15px; font-family: 'DejaVu Sans';">
                                                    {{ $booking_room->room_name }}
                                                </span>
                                                <span style="font-size:16px; font-weight:400; margin:9px 0 15px; font-family: 'DejaVu Sans';">
                                                    ({{ ucwords(strtolower($booking_room->board_name)) }})
                                                </span>
                                            </td>
                                        </tr>
                                        @php
                                            $room_booking_detail = $booking->details->skip($index)->first();
                                            if (!$room_booking_detail) {
                                                $room_booking_detail = $booking->primary_details;
                                            }
                                        @endphp
                                        @if ($room_booking_detail)
                                            <tr>
                                                <td valign="top" style="padding-right:12px; padding-top: 12px; width: 50%;" colspan="2">
                                                    <span style="font-size:16px; font-weight:700; margin:9px 0 15px;">
                                                        User Name:
                                                    </span>
                                                    <span style="font-size:16px; font-weight:400; margin:9px 0 15px;">
                                                        {{ $room_booking_detail->first_name }} {{ $room_booking_detail->last_name }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                        @if ($booking_room->supplier_confirmation_code)
                                            <tr>
                                                <td valign="top" style="padding-right:12px; padding-top: 12px; width: 50%;" colspan="2">
                                                    <span style="font-size:16px; font-weight:700; margin:9px 0 15px;">
                                                        Supplier Confirmation Code:
                                                    </span>
                                                    <span style="font-size:16px; font-weight:400; margin:9px 0 15px;">
                                                        {{ $booking_room->supplier_confirmation_code }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>

                                            <!-- LEFT CONTENT -->
                                            <td valign="top" style="padding-right:12px;width: 100%;" colspan="2">

                                                @if ($booking_room->cancellation_policies->count() > 0)
                                                    <table cellspacing="0" cellpadding="0"
                                                        style="border-collapse:collapse;width: 100%;">
                                                        <tr>
                                                            <td valign="middle"
                                                                style="vertical-align:middle; font-size:14px; padding-top: 15px; width: 100%;">
                                                                <span style="font-weight:700;">Cancellation Policy:</span>
                                                                {{ $booking_room->cancellation_policies->map(fn($p) => \Carbon\Carbon::parse($p->from)->format('d M Y h:i A'))->implode(' | ') }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                @endif

                                                @if ($booking_room->rate_comments)
                                                    <div style="border:1px solid #dbc8b6; border-radius:6px; margin-top:12px;">
                                                        <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                                            <tr>
                                                                <td valign="middle"
                                                                    style="vertical-align:middle; font-size:14px; padding: 15px 15px 0 15px; width: 100%;">
                                                                    <span style="font-weight:700;">
                                                                        Rate Comment:
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td valign="middle"
                                                                    style="vertical-align:middle; font-size:14px; padding: 10px 15px 15px 15px; width: 100%;">
                                                                    {{ $booking_room->rate_comments }}
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                @endif
                                            </td>

                                        </tr>
                                    </table>

                                </td>
                            </tr>
                        </table>
                        </div>
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
