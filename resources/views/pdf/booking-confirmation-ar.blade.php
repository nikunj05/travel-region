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
    <div id="pdf-footer">
        الدفع من خلال <strong>{{ $booking->supplier_name }}</strong>، الذي يعمل كوكيل لشركة تشغيل الخدمة، يمكن تقديم تفاصيلها عند الطلب. الرقم الضريبي: <strong>{{ $booking->vat_number }}</strong> المرجع: <strong>{{ $booking->booking_reference }}</strong>
    </div>
    <div
        style="display: flex; justify-content:center; padding:0px 0; font-family:'Amiri', sans-serif; direction:rtl;">
        <div style="width:100%; max-width:95%; background:white; padding:10px; direction:rtl; transform:scale(0.94); transform-origin: top right;">
            <!-- width:794px -->

            <!-- HEADER -->
            <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 10px">
                <tr>
                    <!-- RIGHT: Booking Info -->
                    <td
                        style="width: 40%;vertical-align: top;text-align: left;font-family: 'Amiri', sans-serif;">
                        <div style="font-size: 13px; direction: ltr; text-align: left">
                            معرف الحجز: <strong>{{ $booking->order }}</strong>
                        </div>

                        <div style="font-size: 13px; direction: ltr; text-align: left">
                            مرجع أسرة الفندق: <strong>{{ $booking->booking_reference }}</strong>
                        </div>

                        <div style="margin-top: 6px; font-size: 12px; direction: ltr">
                            (تم الحجز على {{ $booking->created_at->format('d M Y, h:i A') }})
                        </div>
                    </td>

                    <!-- CENTER: Title -->
                    <td
                        style="
                        width: 25%;
                        text-align: center;
                        vertical-align: top;
                        font-size: 22px;
                        font-weight: 700;
                        direction: ltr;
                        color: #0b343a;">
                        <span>قسيمة الحجز</span>
                    </td>

                    <!-- LEFT: Logo -->
                    <td style="width: 35%; vertical-align: top; text-align: right">
                        <img src="{{ public_path('images/logo.png') }}" width="145" style="margin: 0; padding: 0" />
                    </td>
                </tr>
            </table>

            <main style="color: #0b343a;">
                <div style="border: 1px solid #dbc8b6; border-radius: 6px;">
                    <table width="100%" cellspacing="0" cellpadding="0"
                        style="
                padding: 10px 10px 0;
                border-bottom: 1px solid #dbc8b6;
                font-family: 'Amiri', sans-serif;
                ">
                        <tr>
                            <!-- LEFT IMAGE (FIRST IN HTML) -->
                            <td valign="top" width="30%" style="text-align: left; padding-right: 10px">
                                <img src="{{ public_path('images/thank-you-icon.png') }}" width="110" height="110"
                                    style="object-fit: cover; rotate: 20deg; transform: rotate(-20deg);" />
                            </td>

                            <!-- RIGHT CONTENT (SECOND IN HTML) -->
                            <td valign="top" width="70%" style="text-align: right; padding-left: 24px">
                                <div
                                    style="
                        font-size: 22px;
                        font-weight: 700;
                        margin-bottom: 6px;
                        ">
                                    {{ $booking->hotel_name }}
                                </div>

                                <!-- Stars -->
                                <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 10px">
                                    <tr>
                                        <!-- EMPTY SPACE -->
                                        <td width="100%"></td>

                                        @php
                                            $start_count = 0;
                                            if ($booking->category) {
                                                $start_count = str_replace([' STARS', ' STAR'], '', $booking->category);
                                            }
                                        @endphp
                                        <td nowrap>
                                            @for ($i = 0; $i < $start_count; $i++)
                                                <img src="{{ public_path('images/star.svg') }}" alt="star"
                                                    width="16">
                                            @endfor
                                        </td>
                                    </tr>
                                </table>

                                <p style="font-size: 14px; margin: 0 0 8px 0">
                                    {{ $booking->address }}
                                </p>

                                <p style="font-size: 14px; margin: 0 0 4px 0">
                                    نوع الإقامة: {{ $booking->accommodation_type }}
                                </p>

                                @if ($booking->phone)
                                    @foreach (json_decode($booking->phone) as $phone)
                                        @php
                                            $type = 'Phone';
                                            if ($phone->phoneType == 'PHONEBOOKING') {
                                                $type = 'رقم هاتف الحجز';
                                            } elseif ($phone->phoneType == 'PHONEHOTEL') {
                                                $type = 'هاتف الفندق';
                                            } elseif ($phone->phoneType == 'PHONEMANAGEMENT') {
                                                $type = 'هاتف الإدارة';
                                            } elseif ($phone->phoneType == 'PHONEHOTEL') {
                                                $type = 'هاتف الفندق';
                                            } elseif ($phone->phoneType == 'FAXNUMBER') {
                                                $type = 'رقم الفاكس';
                                            }
                                        @endphp
                                        <div style="margin-bottom:4px;">
                                            <p style="font-size: 14px; margin: 0 0 3px 0">
                                                {{ $type }}: {{ $phone->phoneNumber }}
                                            </p>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    </table>

                    <div style="padding: 0 10px; font-family: 'Amiri', sans-serif">
                        <!-- ================= ROW 1 ================= -->
                        <table width="100%" cellspacing="0" cellpadding="0"
                            style="
                    border-collapse: collapse;
                    table-layout: fixed;
                    text-align: right;
                ">
                            <tr style="border-bottom:1px solid #dbc8b6;">
                                <!-- CHECK-OUT (RIGHT) -->
                                <td valign="top" align="right" style="padding: 10px 8px">
                                    <div
                                        style="
                        font-weight: 700;
                        font-size: 14px;
                        margin-bottom: 6px;">
                                        الدفع
                                    </div>
                                    <div style="font-size: 16px; font-weight: 700">
                                        {{ $booking->check_out->format('D') }}, {{ $booking->check_out->format('d M') }}
                                        <span style="font-size: 14px; font-weight: 400">{{ $booking->check_out->format('Y') }}</span>
                                    </div>
                                </td>

                                <!-- CHECK-IN (CENTER) -->
                                <td valign="top" align="right" style="padding: 10px 8px">
                                    <div
                                        style="
                        font-weight: 700;
                        font-size: 14px;
                        margin-bottom: 6px;">
                                        تحقق في
                                    </div>
                                    <div style="font-size: 16px; font-weight: 700">
                                        {{ $booking->check_in->format('D') }}, {{ $booking->check_in->format('d M') }}
                                        <span style="font-size: 14px; font-weight: 400">{{ $booking->check_in->format('Y') }}</span>
                                    </div>
                                </td>

                                <!-- NIGHTS (LEFT) -->
                                <td valign="top" align="right" style="padding: 10px 8px; text-align: right">
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="right"
                                                style="
                            padding-right: 6px;
                            font-weight: 700;
                            font-size: 15px;">
                                                {{ $booking->nights }} ليالي الإقامة
                                            </td>
                                            <td align="right" style="width: 20px">
                                                <img src="{{ public_path('images/calendar.svg') }}" width="20px" />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- ================= ROW 2 ================= -->
                            <tr style="border-bottom:1px solid #dbc8b6;">
                                <!-- PRIMARY GUEST (RIGHT) -->
                                <td colspan="2" valign="top" align="right" style="padding: 10px 8px">
                                    <div
                                        style="
                        font-size: 15px;
                        font-weight: 700;
                        margin-bottom: 6px;
                      ">
                                        {{ $booking->primary_details->first_name . ' ' . $booking->primary_details->last_name }}
                                        <span style="font-size: 14px; font-weight: 400">(الضيف الرئيسي)</span>
                                    </div>

                                    <div style="margin-top: 8px; font-size: 14px">
                                        {{ $booking->primary_details->email }}، {{ $booking->primary_details->country_code . $booking->primary_details->phone }}
                                    </div>
                                </td>

                                <!-- GUEST COUNT (LEFT) -->
                                <td valign="top" style="padding: 10px 8px; text-align: right">
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="right" style="padding-right: 6px">
                                                <div style="font-weight: 700; font-size: 15px">
                                                    {{ $booking->adults + $booking->children }} الضيوف
                                                </div>
                                                <div style="font-size: 13px; margin-top: 4px">
                                                    ({{ $booking->adults }} الكبار &amp; {{ $booking->children }} أطفال)
                                                    @if($booking->child_age)
                                                        <br>
                                                        <strong>السنوات:</strong>
                                                        @foreach(json_decode($booking->child_age) as $age)
                                                            {{ $age }}@if(!$loop->last), @endif
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </td>
                                            <td align="right" style="width: 20px">
                                                <img src="{{ public_path('images/user.svg') }}" width="20px" />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- ================= ROW 3 ================= -->
                            <tr>
                                <!-- ROOM COUNT (LEFT) -->
                                <td colspan="2" valign="top" style="padding:10px 8px 0px;">
                                    <div style="font-size:16px; font-weight:700; margin-bottom:8px;">
                                        {{-- Premier Deluxe Room --}}
                                    </div>
                                </td>
                                <td valign="top" style="padding: 10px 8px; text-align: right">
                                    <table width="100%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="right"
                                                style="padding-right: 6px;font-weight: 700;font-size: 17px;">
                                                {{ $booking->rooms }} غرفة
                                            </td>
                                            <td align="right" style="width: 20px">
                                                <img src="{{ public_path('images/door.svg') }}" width="20px" />
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                            </tr>
                        </table>

                    </div>
                </div>

                <div style="text-align:right;">
                    <h4
                        style="
                    font-size: 14px;
                    font-weight: 700;
                    margin: 8px 0 8px;
                    text-align: right;
                ">
                        الغرف
                    </h4>

                    @foreach ($booking->booking_room as $index => $booking_room)
                        <div style="border:1px solid #dbc8b6; border-radius:6px; margin-bottom:8px; padding:0;">
                        <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                            <tr>
                                <td style="padding:10px 12px 12px;" align="right">
                                    <!-- MAIN CONTENT TABLE -->
                                    <table width="100%" cellspacing="0" cellpadding="0" align="right" style="border-collapse:collapse;">
                                        <tr>
                                            <td valign="top" align="right">
                                                <!-- ROOM TITLE -->
                                                <div
                                                    style="
                                font-size: 14px;
                                font-weight: 700;
                                margin: 6px 0 10px;
                                text-align: right;
                            ">
                                                    <span style="font-size:14px; font-weight:700; margin:6px 0 10px;">
                                                        {{ $booking_room->room_name }}
                                                    </span>
                                                    <span style="font-size:14px; font-weight:400; margin:6px 0 10px;">
                                                        ({{ ucwords(strtolower($booking_room->board_name)) }})
                                                    </span>
                                                </div>

                                                <!-- DETAILS -->
                                                <table width="100%" cellspacing="0" cellpadding="0" align="right" style="border-collapse:collapse;">
                                                    @php
                                                        $room_booking_detail = $booking->details->skip($index)->first();
                                                        if (!$room_booking_detail) {
                                                            $room_booking_detail = $booking->primary_details;
                                                        }
                                                    @endphp
                                                    @if ($room_booking_detail)
                                                        <tr>
                                                            <td align="right" style="font-size: 14px">
                                                                <span style="font-size:14px; font-weight:700; margin:9px 0 15px;">
                                                                    الضيوف:
                                                                </span>
                                                                <span style="font-size:14px; font-weight:400; margin:9px 0 15px;">
                                                                    {{ $room_booking_detail->first_name }} {{ $room_booking_detail->last_name }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if ($booking_room->cancellation_policies->count() > 0)
                                                        <tr>
                                                            <td align="right" style="font-size: 14px; padding-top: 6px">
                                                                <strong>سياسة الإلغاء:</strong>
                                                                {{ $booking_room->cancellation_policies->map(fn($p) => \Carbon\Carbon::parse($p->from)->format('d M Y h:i A'))->implode(' | ') }}
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if ($booking_room->rate_comments)
                                                        <tr>
                                                            <td align="right" style="font-size: 8px; padding-top: 6px;">
                                                                <div style="border:1px solid #dbc8b6; border-radius:6px; margin-top:8px;">
                                                                    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                                                        <tr>
                                                                            <td align="right" style="font-size:12px; padding:10px 10px 0 10px;">
                                                                                <strong>ملاحظات</strong>
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

                <!-- FOOTER -->
                <table width="100%" cellspacing="0" cellpadding="0"
                    style="margin-top: 20px; margin-bottom: 24px; border-collapse: collapse">
                    <tr>
                        <td align="right" style="padding: 0">
                            <!-- TITLE -->
                            <table width="100%" cellspacing="0" cellpadding="0" align="right">
                                <tr>
                                    <td align="right" style="padding-bottom: 12px">
                                        <span style="font-size: 24px;font-weight: 700;color: #f6be00;">
                                            Travel Region Support
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- EMAIL -->
                            <table width="100%" cellspacing="0" cellpadding="0" align="right">
                                <tr>
                                    <td align="right" style="color: #156874">
                                        <a href="mailto:info@travelregions.sa"
                                            style="color: #156874; font-size: 16px; text-decoration: none;">
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
