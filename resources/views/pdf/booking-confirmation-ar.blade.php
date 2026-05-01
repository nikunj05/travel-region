<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Invoice</title>

    <style>
        @font-face {
            font-family: 'Amiri';
            font-style: normal;
            font-weight: 400;
            src: url("{{ public_path('fonts/Amiri-Regular.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'Amiri';
            font-style: normal;
            font-weight: 700;
            src: url("{{ public_path('fonts/Amiri-Bold.ttf') }}") format('truetype');
        }

        body {
            font-family: 'Amiri', sans-serif !important;
            margin: 0;
            padding: 20px;
            direction: rtl;
            text-align: right;
            line-height: 1;
        }

        p,
        td,
        div,
        span {
            line-height: 1;
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

<body style="margin: 0; padding: 0; box-sizing: border-box; direction: rtl">

    {{-- ===== FOOTER (fixed, shown on every page) ===== --}}
    <div id="pdf-footer">
        {{ $labels['footer_payment'] }} <strong>{{ $booking->supplier_name }}</strong>،
        {{ $labels['footer_agent'] }}
        {{ $labels['footer_vat'] }} <strong>{{ $booking->vat_number }}</strong>
        {{ $labels['footer_ref'] }} <strong>{{ $booking->booking_reference }}</strong>
    </div>

    <div style="display:flex; justify-content:center; padding:0; font-family:'Amiri',sans-serif; direction:rtl;">
        <div style="width:100%; max-width:95%; background:white; padding:10px; direction:rtl; transform:scale(0.94); transform-origin:top right;">

            {{-- ===== HEADER ===== --}}
            <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:10px">
                <tr>
                    {{-- Booking Info --}}
                    <td style="width:40%; vertical-align:top; text-align:left; font-family:'Amiri',sans-serif;">
                        <div style="font-size:13px; direction:ltr; text-align:left">
                            {{ $labels['booking_id'] }} <strong>{{ $booking->order }}</strong>
                        </div>
                        <div style="font-size:13px; direction:ltr; text-align:left">
                            {{ $labels['hotel_ref'] }} <strong>{{ $booking->booking_reference }}</strong>
                        </div>
                        <div style="margin-top:6px; font-size:12px; direction:ltr">
                            ({{ $labels['booked_on'] }} {{ $booking->created_at->format('d M Y, h:i A') }})
                        </div>
                    </td>

                    {{-- Title --}}
                    <td style="width:25%; text-align:center; vertical-align:top; font-size:22px; font-weight:700; direction:ltr; color:#0b343a;">
                        <span>{{ $labels['booking_voucher'] }}</span>
                    </td>

                    {{-- Logo --}}
                    <td style="width:35%; vertical-align:top; text-align:right">
                        <img src="{{ public_path('images/logo.png') }}" width="145" style="margin:0; padding:0" />
                    </td>
                </tr>
            </table>

            <main style="color:#0b343a;">
                <div style="border:1px solid #dbc8b6; border-radius:6px;">
                    <table width="100%" cellspacing="0" cellpadding="0"
                        style="padding:10px 10px 0; border-bottom:1px solid #dbc8b6; font-family:'Amiri',sans-serif;">
                        <tr>
                            {{-- Thank You Icon --}}
                            <td valign="top" width="30%" style="text-align:left; padding-right:10px">
                                <img src="{{ public_path('images/thank-you-icon.png') }}" width="110" height="110"
                                    style="object-fit:cover; transform:rotate(-20deg);" />
                            </td>

                            {{-- Hotel Info --}}
                            <td valign="top" width="70%" style="text-align:right; padding-left:24px">
                                <div style="font-size:22px; font-weight:700; margin-bottom:6px;">
                                    {{ $booking->hotel_name }}
                                </div>

                                {{-- Stars --}}
                                <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:10px">
                                    <tr>
                                        <td width="100%"></td>
                                        @php
                                            $start_count = 0;
                                            if ($booking->category) {
                                                $start_count = (int) str_replace([' STARS', ' STAR'], '', $booking->category);
                                            }
                                        @endphp
                                        <td nowrap>
                                            @for ($i = 0; $i < $start_count; $i++)
                                                <img src="{{ public_path('images/star.svg') }}" alt="star" width="16">
                                            @endfor
                                        </td>
                                    </tr>
                                </table>

                                <p style="font-size:14px; margin:0 0 8px 0">{{ $booking->address }}</p>

                                <p style="font-size:14px; margin:0 0 4px 0">
                                    {{ $labels['accommodation_type'] }}: {{ $booking->accommodation_type }}
                                </p>

                                {{-- Phone Numbers --}}
                                @if ($booking->phone)
                                    @foreach (json_decode($booking->phone) as $phone)
                                        @php
                                            // Use pre-shaped labels from controller
                                            $type = match($phone->phoneType) {
                                                'PHONEBOOKING'    => $labels['phone_booking'],
                                                'PHONEHOTEL'      => $labels['phone_hotel'],
                                                'PHONEMANAGEMENT' => $labels['phone_management'],
                                                'FAXNUMBER'       => $labels['phone_fax'],
                                                default           => 'Phone',
                                            };
                                        @endphp
                                        <div style="margin-bottom:4px;">
                                            <p style="font-size:14px; margin:0 0 3px 0">
                                                {{ $type }}: {{ $phone->phoneNumber }}
                                            </p>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    </table>

                    <div style="padding:0 10px; font-family:'Amiri',sans-serif">

                        {{-- ===== ROW 1: Check-in / Check-out / Nights ===== --}}
                        <table width="100%" cellspacing="0" cellpadding="0"
                            style="border-collapse:collapse; table-layout:fixed; text-align:right;">
                            <tr style="border-bottom:1px solid #dbc8b6;">

                                {{-- Check-out --}}
                                <td valign="top" align="right" style="padding:10px 8px">
                                    <div style="font-weight:700; font-size:14px; margin-bottom:6px;">
                                        {{ $labels['check_out'] }}
                                    </div>
                                    <div style="font-size:16px; font-weight:700">
                                        {{ $booking->check_out->format('D') }}, {{ $booking->check_out->format('d M') }}
                                        <span style="font-size:14px; font-weight:400">{{ $booking->check_out->format('Y') }}</span>
                                    </div>
                                </td>

                                {{-- Check-in --}}
                                <td valign="top" align="right" style="padding:10px 8px">
                                    <div style="font-weight:700; font-size:14px; margin-bottom:6px;">
                                        {{ $labels['check_in'] }}
                                    </div>
                                    <div style="font-size:16px; font-weight:700">
                                        {{ $booking->check_in->format('D') }}, {{ $booking->check_in->format('d M') }}
                                        <span style="font-size:14px; font-weight:400">{{ $booking->check_in->format('Y') }}</span>
                                    </div>
                                </td>

                                {{-- Nights --}}
                                <td valign="top" align="right" style="padding:10px 8px; text-align:right">
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="right" style="padding-right:6px; font-weight:700; font-size:15px;">
                                                {{ $booking->nights }} {{ $labels['nights'] }}
                                            </td>
                                            <td align="right" style="width:20px">
                                                <img src="{{ public_path('images/calendar.svg') }}" width="20px" />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            {{-- ===== ROW 2: Guest Info ===== --}}
                            <tr style="border-bottom:1px solid #dbc8b6;">

                                {{-- Primary Guest --}}
                                <td colspan="2" valign="top" align="right" style="padding:10px 8px">
                                    <div style="font-size:15px; font-weight:700; margin-bottom:6px;">
                                        {{ $booking->primary_details->first_name . ' ' . $booking->primary_details->last_name }}
                                        <span style="font-size:14px; font-weight:400">({{ $labels['primary_guest'] }})</span>
                                    </div>
                                    <div style="margin-top:8px; font-size:14px">
                                        {{ $booking->primary_details->email }}، {{ $booking->primary_details->country_code . $booking->primary_details->phone }}
                                    </div>
                                </td>

                                {{-- Guest Count --}}
                                <td valign="top" style="padding:10px 8px; text-align:right">
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="right" style="padding-right:6px">
                                                <div style="font-weight:700; font-size:15px">
                                                    {{ $booking->adults + $booking->children }} {{ $labels['guests'] }}
                                                </div>
                                                <div style="font-size:13px; margin-top:4px">
                                                    ({{ $booking->adults }} {{ $labels['adults'] }} &amp; {{ $booking->children }} {{ $labels['children'] }})
                                                    @if ($booking->child_age)
                                                        <br>
                                                        <strong>{{ $labels['ages'] }}</strong>
                                                        @foreach (json_decode($booking->child_age) as $age)
                                                            {{ $age }}@if (!$loop->last), @endif
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </td>
                                            <td align="right" style="width:20px">
                                                <img src="{{ public_path('images/user.svg') }}" width="20px" />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            {{-- ===== ROW 3: Room Count ===== --}}
                            <tr>
                                <td colspan="2" valign="top" style="padding:10px 8px 0px;"></td>
                                <td valign="top" style="padding:10px 8px; text-align:right">
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="right" style="padding-right:6px; font-weight:700; font-size:17px;">
                                                {{ $booking->rooms }} {{ $labels['room'] }}
                                            </td>
                                            <td align="right" style="width:20px">
                                                <img src="{{ public_path('images/door.svg') }}" width="20px" />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>

                {{-- ===== ROOMS SECTION ===== --}}
                <div style="text-align:right;">
                    <h4 style="font-size:14px; font-weight:700; margin:8px 0 8px; text-align:right;">
                        {{ $labels['rooms_title'] }}
                    </h4>

                    @foreach ($booking->booking_room as $index => $booking_room)
                        @php
                            $boardName = $booking_room->board_name;
                            // Only apply ucwords if it's not Arabic text
                            if ($boardName && !preg_match('/[\x{0600}-\x{06FF}]/u', $boardName)) {
                                $boardName = ucwords(strtolower($boardName));
                            }

                            $room_booking_detail = $booking->details->skip($index)->first()
                                ?? $booking->primary_details;
                        @endphp

                        <div style="border:1px solid #dbc8b6; border-radius:6px; margin-bottom:8px; padding:0;">
                            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                <tr>
                                    <td style="padding:10px 12px 12px;" align="right">
                                        <table width="100%" cellspacing="0" cellpadding="0" align="right" style="border-collapse:collapse;">
                                            <tr>
                                                <td valign="top" align="right">

                                                    {{-- Room Title --}}
                                                    <div style="font-size:14px; font-weight:700; margin:6px 0 10px; text-align:right;">
                                                        <span style="font-size:14px; font-weight:700;">
                                                            {{ $booking_room->room_name }}
                                                        </span>
                                                        <span style="font-size:14px; font-weight:400;">
                                                            ({{ $boardName }})
                                                        </span>
                                                    </div>

                                                    {{-- Room Details --}}
                                                    <table width="100%" cellspacing="0" cellpadding="0" align="right" style="border-collapse:collapse;">

                                                        @if ($room_booking_detail)
                                                            <tr>
                                                                <td align="right" style="font-size:14px">
                                                                    <span style="font-size:14px; font-weight:700;">
                                                                        {{ $labels['room_guests'] }}
                                                                    </span>
                                                                    <span style="font-size:14px; font-weight:400;">
                                                                        {{ $room_booking_detail->first_name }} {{ $room_booking_detail->last_name }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        @if ($booking_room->cancellation_policies->count() > 0)
                                                            <tr>
                                                                <td align="right" style="font-size:14px; padding-top:6px">
                                                                    <strong>{{ $labels['cancellation_policy'] }}</strong>
                                                                    {{ $booking_room->cancellation_policies->map(fn($p) => \Carbon\Carbon::parse($p->from)->format('d M Y h:i A'))->implode(' | ') }}
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        @if ($booking_room->rate_comments)
                                                            <tr>
                                                                <td align="right" style="padding-top:6px;">
                                                                    <div style="border:1px solid #dbc8b6; border-radius:6px; margin-top:8px;">
                                                                        <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                                                            <tr>
                                                                                <td align="right" style="font-size:12px; padding:10px 10px 0 10px;">
                                                                                    <strong>{{ $labels['notes'] }}</strong>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td align="right" style="font-size:12px; padding:8px 10px 10px 10px;">
                                                                                    {{ $booking_room->rate_comments }}
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                </div>

                {{-- ===== FOOTER CONTACT ===== --}}
                <table width="100%" cellspacing="0" cellpadding="0"
                    style="margin-top:20px; margin-bottom:24px; border-collapse:collapse">
                    <tr>
                        <td align="right" style="padding:0">
                            <table width="100%" cellspacing="0" cellpadding="0" align="right">
                                <tr>
                                    <td align="right" style="padding-bottom:12px">
                                        <span style="font-size:24px; font-weight:700; color:#f6be00;">
                                            Travel Regions Support
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            <table width="100%" cellspacing="0" cellpadding="0" align="right">
                                <tr>
                                    <td align="right" style="color:#156874">
                                        <a href="mailto:info@travelregions.sa"
                                            style="color:#156874; font-size:16px; text-decoration:none;">
                                            info@travelregions.sa
                                        </a>
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
