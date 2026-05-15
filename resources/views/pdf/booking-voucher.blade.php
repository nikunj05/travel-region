<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Voucher</title>

    <style>
        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('fonts/Montserrat/Montserrat-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('fonts/Montserrat/Montserrat-Medium.ttf') }}') format('truetype');
            font-weight: 500;
            font-style: normal;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('fonts/Montserrat/Montserrat-SemiBold.ttf') }}') format('truetype');
            font-weight: 600;
            font-style: normal;
        }

        @font-face {
            font-family: 'Montserrat';
            src: url('{{ public_path('fonts/Montserrat/Montserrat-Bold.ttf') }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        @font-face {
            font-family: 'Tajawal';
            src: url('{{ public_path('fonts/Tajawal/Tajawal-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'Tajawal';
            src: url('{{ public_path('fonts/Tajawal/Tajawal-Medium.ttf') }}') format('truetype');
            font-weight: 500;
            font-style: normal;
        }

        @font-face {
            font-family: 'Tajawal';
            src: url('{{ public_path('fonts/Tajawal/Tajawal-Bold.ttf') }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        @page {
            margin: 0;
        }
    </style>
</head>

<body
    style="margin: 0; padding: 0; background-color: #fff; color: #000; line-height: 1.4; font-family: 'Montserrat', 'Tajawal', sans-serif;">
    @php
        $labels = $labels ?? [];
        $primaryGuest = $booking->primary_details;
        $bookingRooms = $booking->booking_room ?? collect();
        $roomCount = $bookingRooms->count() > 0 ? $bookingRooms->count() : (int) ($booking->rooms ?? 0);

        // Group rooms by type and count occurrences
        $roomsByType = [];
        foreach ($bookingRooms as $room) {
            $roomType = trim((string) ($room->room_name ?? ''));
            if ($roomType !== '') {
                if (!isset($roomsByType[$roomType])) {
                    $roomsByType[$roomType] = 0;
                }
                $roomsByType[$roomType]++;
            }
        }
        $firstRoom = $bookingRooms->first();

        $starsCount = 0;
        if (!empty($booking->category)) {
            $starsCount = (int) str_replace([' STARS', ' STAR'], '', $booking->category);
        }

        $statusMapAr = $statusMapAr ?? [
            'confirmed' => $labels['status_confirmed'] ?? 'مؤكد',
            'cancelled' => $labels['status_cancelled'] ?? 'ملغي',
            'pending' => $labels['status_pending'] ?? 'قيد الانتظار',
        ];

        $statusEn = strtoupper((string) ($booking->status ?? 'confirmed'));
        $statusAr = $statusMapAr[$booking->status ?? 'confirmed'] ?? ($labels['status_confirmed'] ?? 'مؤكد');

        $childAges = [];
        if (!empty($booking->child_age)) {
            $decodedChildAges = json_decode($booking->child_age, true);
            if (is_array($decodedChildAges)) {
                $childAges = $decodedChildAges;
            }
        }

        $cancellationDate = optional(
            $bookingRooms
                ->flatMap(fn($room) => $room->cancellation_policies)
                ->sortBy('from')
                ->first()
        )->from;

        $patterns = [
            '/[\s\.\-–—]*\bLGTBIQ\s*friendly\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*friendly\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBT\s*friendly\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bGay\s*friendly\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*amigable\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*freundlich\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*amical\b[\s\.\-–—]*/i',
            '/[\s\.\-–—]*\bLGBTQ\s*友好\b[\s\.\-–—]*/u',
            '/[\s\.\-–—]*\bLGTBIQ\b[\s\.\-–—]*/i',
        ];

        $rateComments = trim((string) ($firstRoom?->rate_comments ?? ''));
        $rateComments = preg_replace($patterns, ' ', $rateComments);
        $rateComments = preg_replace('/\s{2,}/', ' ', (string) $rateComments);
        $rateComments = trim((string) $rateComments, " \t\n\r\0\x0B,;:-");
        $rateComments = $rateComments !== '' ? $rateComments : '-';
    @endphp

    <div style="width: 100%; margin: 0; background: #fff;">
        <!-- HEADER -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0px;">
            <tr>
                <td style="vertical-align: middle; width: 60%; padding: 50px 10px 0 30px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="vertical-align: middle; width: auto;">
                                <img src="{{ public_path('images/travel-rigion-logo.webp') }}"
                                    height="60" alt="Travel Regions Logo" />
                            </td>
                        </tr>
                    </table>
                    <div
                        style="margin-top: 0px; font-family: 'Tajawal'; font-size: 30px; text-align: center; font-weight: 700; color: #132358;">
                        {{ $labels['voucher_title_ar'] ?? 'قسيمة حجز فندق' }}</div>
                    <div
                        style="font-size: 22px; font-weight: 700; text-align: center; color: #ad9861; margin-top: 5px; display: block;">
                        HOTEL BOOKING VOUCHER</div>
                    <div style="text-align: center; margin-top: 10px;">
                        <img src="{{ public_path('images/ornament.svg') }}" width="100%"
                            alt="divider" />
                    </div>
                </td>
                <td style="vertical-align: top; width: 40%; text-align: right; color: white;">
                    <div>
                        <table align="top" style="width: auto; border-collapse: collapse; margin-bottom: -2px;">
                            <tr>
                                <td style="vertical-align: middle; text-align:center;">
                                    <img src="{{ public_path('images/hotel-header-image.png') }}"
                                        alt="Travel Regions Logo" style="height: 230px; object-fit: cover;" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- MAIN INFO CARD -->
        <div style="padding: 0px 26px;">
            <div
                style="border: 1px solid #b0b9c8; border-radius: 40px; margin-bottom: 10px; padding: 10px; background: #fff;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <!-- Left: Building Icon -->
                        <td style="width: 70px; vertical-align: middle;">
                            <div
                                style="background: #132358; width: 70px; height: 70px; border-radius: 42px; text-align: center; overflow: hidden;">
                                <table style="width: 70px; height: 70px; border-collapse: collapse; margin: 0 auto;">
                                    <tr>
                                        <td
                                            style="vertical-align: middle; text-align: center; height: 70px; padding: 0; line-height: 1;">
                                            <img src="{{ public_path('images/cityscape.svg') }}"
                                                width="45" style="vertical-align: middle; display: inline-block;">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>

                        <!-- Middle: Hotel Details -->
                        <td style="padding-left: 23px; vertical-align: middle;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="vertical-align: middle; display: inline-block; padding: 0px;">
                                        <div
                                            style="font-family: 'Tajawal'; font-size: 15px; color: #132358; font-weight: 700; line-height: 1; text-align: center;">
                                            {{ $labels['hotel_information_ar'] ?? 'معلومات الفندق' }}</div>
                                        <div
                                            style="font-size: 15px; color: #132358; font-weight: 700; text-align: center;">
                                            HOTEL INFORMATION</div>
                                        <div style="margin-top: 1px; height: 2px;">
                                            <img src="{{ public_path('images/tapered_line.svg') }}"
                                                width="100%" height="2" style="display: block;">
                                        </div>
                                    </td>
                                    <td
                                        style="text-align: left; vertical-align: bottom; display: inline-block; padding: 0 5px 0 15px;  ">
                                        @for ($i = 0; $i < $starsCount; $i++)
                                            <img src="{{ public_path('images/star_gold.svg') }}" width="31"
                                                style="margin-left: -9px;">
                                        @endfor
                                    </td>
                                </tr>
                            </table>

                            <div style="font-size: 23px; font-weight: 700; color: #132358; margin: 0; line-height: 1;">
                                {{ $booking->hotel_name }}</div>

                            <div style="font-size: 14px; color: #000000; font-weight: 500; margin-top: 5px;">
                                <img src="{{ public_path('images/pin.svg') }}" width="18"
                                    style=" margin-right: 5px;">
                                <span style="vertical-align: middle;">{{ $booking->address }}</span>
                            </div>
                        </td>

                        <!-- Right: Booking Status -->
                        <td style="width: 140px; text-align: right; vertical-align: middle;  padding-left: 20px;">
                            <div style="text-align: center;">
                                <div
                                    style=" font-family: 'Tajawal'; color: #132358; font-size: 12px; line-height: 1; font-weight: 700;">
                                    {{ $labels['booking_status_ar'] ?? 'حالة الحجز' }}</div>
                                <div style="color: #132358; font-size: 12px; font-weight: 700; margin-bottom: 10px;">
                                    BOOKING STATUS</div>

                                <div
                                    style="font-family: 'Tajawal'; color: #00bf15; font-size: 16px; font-weight: 700; margin-top: 5px;">
                                    {{ $statusAr }}</div>
                                <div style="color: #00bf15; font-size: 16px; font-weight: 700; letter-spacing: 0.5px;">
                                    {{ $statusEn }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- LEAD GUEST CARD -->
            <div
                style="border: 1px solid #b0b9c8; border-radius: 40px; margin-bottom: 10px; padding: 10px; background: #fff;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <!-- Icon -->

                        <td style="width: 70px; vertical-align: middle;">
                            <div
                                style="background: #132358; width: 70px; height: 70px; border-radius: 42px; text-align: center; overflow: hidden;">
                                <table style="width: 70px; height: 70px; border-collapse: collapse; margin: 0 auto;">
                                    <tr>
                                        <td
                                            style="vertical-align: middle; text-align: center; height: 70px; padding: 0; line-height: 1;">
                                            <img src="{{ public_path('images/user-2.svg') }}"
                                                width="45" style="vertical-align: middle; display: inline-block;">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>


                        <!-- Label Section -->
                        <td style="padding-left: 23px; vertical-align: middle; width: 180px;">
                            <div
                                style="font-family: 'Tajawal'; font-size: 15px; color: #132358; font-weight: 700; line-height: 1; text-align: center;">
                                {{ $labels['lead_guest_ar'] ?? 'اسم النزيل الرئيس' }}</div>
                            <div
                                style="font-size: 15px; color: #132358; font-weight: 700; text-align: center; margin-top: 0px; line-height: 1;">
                                LEAD GUEST</div>
                            <div
                                style="margin-top: 5px; height: 2px; width: 120px; margin-left: auto; margin-right: auto;">
                                <img src="{{ public_path('images/tapered_line.svg') }}"
                                    width="100%" height="2" style="display: block;">
                            </div>
                        </td>

                        <!-- Guest Name -->
                        <td style="vertical-align: middle; text-align: center; padding-right: 30px;">
                            <div style="font-size: 26px; font-weight: 700; color: #132358; line-height: 1;">
                                {{ trim(($primaryGuest?->first_name ?? '') . ' ' . ($primaryGuest?->last_name ?? '')) }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- STAY DETAILS CARD -->
            <div
                style="border: 1px solid #b0b9c8; border-radius: 40px; margin-bottom: 10px; padding: 10px; background: #fff;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <!-- BLOCK 1: STAY DETAILS & DATES -->
                        <td style="vertical-align: top; width: 58%; padding-right: 10px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <!-- Icon & Title -->
                                    <td style="width: 70px; vertical-align: middle;">
                                        <div
                                            style="background: #132358; width: 70px; height: 70px; border-radius: 42px; text-align: center; overflow: hidden;">
                                            <table
                                                style="width: 70px; height: 70px; border-collapse: collapse; margin: 0 auto;">
                                                <tr>
                                                    <td
                                                        style="vertical-align: middle; text-align: center; height: 70px; padding: 0; line-height: 1;">
                                                        <img src="{{ public_path('images/stay_details_calendar.svg') }}"
                                                            width="44"
                                                            style="vertical-align: middle; display: inline-block;">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                    <td style="padding-left: 15px; vertical-align: middle;">
                                        <div
                                            style="font-family: 'Tajawal'; font-size: 15px; color: #132358; font-weight: 700; line-height: 1;">
                                            {{ $labels['stay_details_ar'] ?? 'تفاصيل الاقامة' }}</div>
                                        <div
                                            style="font-size: 15px; color: #132358; font-weight: 700; margin-top: 5px; line-height: 1;">
                                            STAY DETAILS</div>
                                        <div style="margin-top: 7px; height: 2px; width: 120px;">
                                            <img src="{{ public_path('images/tapered_line.svg') }}"
                                                width="100%" height="2" style="display: block;">
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Dates Row -->
                            <table style="width: 100%; border-collapse: collapse; margin-top: -10px;">
                                <tr>
                                    <td style="width: 50%; padding-right: 0px; padding-left: 30px;">
                                        <table align="bottom" style="border-collapse: collapse;">
                                            <tr style="vertical-align: bottom;">
                                                <td style="padding: 0;"><img
                                                        src="{{ public_path('images/calendar_details.svg') }}"
                                                        width="35" style="margin-bottom: 5px;"></td>
                                                <td style="padding-left: 10px;">
                                                    <div
                                                        style="color: #000; font-size: 12px; font-family: 'Tajawal'; font-weight: 500; text-align: center; margin-bottom: 0px;">
                                                        {{ $labels['check_in_ar'] ?? 'تاريخ الوصول' }}</div>
                                                    <div
                                                        style="color: #000; font-size: 12px; font-weight: 500; text-align: center; margin-bottom: 2px;">
                                                        CHECK-IN</div>
                                                    <div
                                                        style="color: #132358; font-size: 17px; font-weight: 700; line-height: 1;">
                                                        {{ strtoupper($booking->check_in->format('d M Y')) }}</div>
                                                    <div
                                                        style="color: #000; font-size: 14px; font-weight: 500; line-height: 1;">
                                                        {{ strtoupper($booking->check_in->format('l')) }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="width: 50%; padding-left: 20px;">

                                        <table align="center" style="border-collapse: collapse;">
                                            <tr style="vertical-align: bottom;">
                                                <td style="padding: 0;"><img
                                                        src="{{ public_path('images/calendar_details.svg') }}"
                                                        width="35" style="margin-bottom: 5px;"></td>
                                                <td style="padding-left: 10px;">
                                                    <div
                                                        style="color: #000; font-size: 12px; font-family: 'Tajawal'; font-weight: 500; text-align: center; margin-bottom: 0px;">
                                                        {{ $labels['check_out_ar'] ?? 'تاريخ المغادرة' }}</div>
                                                    <div
                                                        style="color: #000; font-size: 12px; font-weight: 500; text-align: center; margin-bottom: 2px;">
                                                        CHECK-OUT</div>
                                                    <div
                                                        style="color: #132358; font-size: 17px; font-weight: 700; line-height: 1;">
                                                        {{ strtoupper($booking->check_out->format('d M Y')) }}</div>
                                                    <div
                                                        style="color: #000; font-size: 14px; font-weight: 500; line-height: 1;">
                                                        {{ strtoupper($booking->check_out->format('l')) }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Divider -->
                        <td style="width: 1px; border-left: 1px solid #b0b9c8; padding: 0;"></td>

                        <!-- BLOCK 2: ROOM INFO -->
                        <td style="vertical-align: middle; width: 22%; text-align: center; padding-left: 10px;">
                            <div
                                style="font-family: 'Tajawal'; font-size: 15px; color: #000; font-weight: 500; line-height: 1;">
                                {{ $labels['rooms_count_ar'] ?? 'عدد الغرف' }}</div>
                            <div style="font-size: 15px; color: #000; font-weight: 500; margin-bottom: 2px;">ROOM</div>
                            <div
                                style="font-size: 24px; font-weight: 700; color: #132358; margin-bottom: 5px; line-height: 1;">
                                {{ $roomCount > 0 ? $roomCount : '-' }}</div>

                            <table>
                                <tr>
                                    <td style="width: 40px; padding-right: 10px;"><img
                                            src="{{ public_path('images/door_blue.svg') }}"
                                            height="65" width="45"
                                            style="margin-bottom: 0px; object-fit: cover;"></td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <div
                                            style="font-family: 'Tajawal'; font-size: 13px; color: #000; font-weight: 500; line-height: 1;">
                                            {{ $labels['room_ar'] ?? 'غرفة' }}</div>
                                        <div style="font-size: 13px; color: #000; font-weight: 500;">ROOM</div>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Divider -->
                        <td style="width: 1px; border-left: 1px solid #b0b9c8; padding: 0;"></td>

                        <!-- BLOCK 3: SERVICES -->
                        <td style="vertical-align: top; width: 20%; text-align: center; padding-left: 10px;">
                            <div
                                style="font-family: 'Tajawal'; font-size: 15px; color: #000; font-weight: 500; line-height: 1;">
                                {{ $labels['services_ar'] ?? 'الخدمات' }}</div>
                            <div style="font-size: 15px; color: #000; font-weight: 500; margin-bottom: 20px;">SERVICE
                            </div>

                            @foreach ($services as $service)
                                <table style="width: 100%; border-collapse: collapse; {{ $loop->first ? 'margin-bottom: 6px;' : '' }}">
                                    <tr>
                                        <td style="text-align: center;">
                                            <div
                                                style="font-size: 11px; text-align: center; color: #00be15; font-weight: 500;">
                                                {{ $service['en'] ?? '-' }}</div>
                                        </td>
                                    </tr>
                                </table>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </div>

            <!-- ROOM & OCCUPANCY CARD -->
            <div
                style="border: 1px solid #b0b9c8; border-radius: 40px; margin-bottom: 12px; padding: 10px 10px 15px 10px; background: #fff;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <!-- BLOCK 1: ROOM DETAILS -->
                        <td style="vertical-align: top; width: 36%;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 70px; vertical-align: top;">
                                        <div
                                            style="background: #132358; width: 70px; height: 70px; border-radius: 42px; text-align: center; overflow: hidden;">
                                            <table
                                                style="width: 70px; height: 70px; border-collapse: collapse; margin: 0 auto;">
                                                <tr>
                                                    <td
                                                        style="vertical-align: middle; text-align: center; height: 70px; padding: 0; line-height: 1;">
                                                        <img src="{{ public_path('images/bed.svg') }}"
                                                            width="43"
                                                            style="vertical-align: middle; display: inline-block;">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                    <td
                                        style="padding-left: 12px; text-align: center; vertical-align: top; display: inline-block;">
                                        <div
                                            style="font-family: 'Tajawal'; font-size: 15px; color: #132358; font-weight: 700; line-height: 1;">
                                            {{ $labels['room_details_ar'] ?? 'تفاصيل الغرفة' }}</div>
                                        <div
                                            style="font-size: 15px; color: #132358; font-weight: 700; margin-top: 5px; line-height: 1;">
                                            ROOM DETAILS</div>
                                        <div style="margin-top: 7px; height: 2px; width: 130px; margin-bottom: 5px;">
                                            <img src="{{ public_path('images/tapered_line.svg') }}"
                                                width="100%" height="2" style="display: block;">
                                        </div>
                                        <div
                                            style="font-family: 'Tajawal'; font-size: 13px; color: #000; font-weight: 500; line-height: 1;">
                                            {{ $labels['room_type_ar'] ?? 'نوع الغرفة' }}</div>
                                        <div
                                            style="font-size: 14px; color: #000; font-weight: 500; line-height: 1; margin-top: 2px;">
                                            ROOM TYPE</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"
                                        style="padding-left: 12px; text-align: center; vertical-align: top; width: 100%;">
                                        @forelse ($roomsByType as $roomType => $count)
                                            <div style="font-size: {{ $loop->first ? '17' : '13' }}px; color: #132358; font-weight: {{ $loop->first ? '700' : '600' }}; margin-top: {{ $loop->first ? '7' : '5' }}px; line-height: 1.2; word-wrap: break-word;">
                                                {{ $count }} X {{ strtoupper($roomType) }}
                                            </div>
                                        @empty
                                            <div style="font-size: 17px; color: #132358; font-weight: 700; margin-top: 7px; line-height: 1;">-</div>
                                        @endforelse
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Divider -->
                        <td style="width: 1px; border-left: 1px solid #b0b9c8; padding: 0;"></td>

                        <!-- BLOCK 2: OCCUPANCY -->
                        <td style="vertical-align: top; width: 25%; text-align: center;">
                            <div
                                style="font-family: 'Tajawal'; font-size: 15px; color: #132358; font-weight: 700; line-height: 1;">
                                {{ $labels['occupancy_ar'] ?? 'عدد الضيوف' }}</div>
                            <div style="font-size: 15px; color: #132358; font-weight: 700; margin-bottom: 15px;">
                                OCCUPANCY</div>

                            <table align="center" style="border-collapse: collapse;">
                                <tr>
                                    <td style="padding-right: 5px;">
                                        <img src="{{ public_path('images/user_blue.svg') }}"
                                            width="50">
                                    </td>
                                    <td style="text-align: left;">
                                        <div
                                            style="font-size: 16px; color: #132358; font-weight: 700; line-height: 1;">
                                            {{ $booking->adults }} {{ $labels['adult_ar'] ?? 'بالغ' }}</div>
                                        <div
                                            style="font-size: 15px; color: #132358; font-weight: 700; line-height: 1;">
                                            {{ $booking->children }} {{ $labels['child_ar'] ?? 'طفل' }},</div>
                                        <div
                                            style="font-size: 12px; color: #132358; font-weight: 700; line-height: 1;">
                                            {{ count($childAges) > 0 ? implode(', ', $childAges) . ' ' . ($labels['years_ar'] ?? 'سنوات') : '-' }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Divider -->
                        <td style="width: 1px; border-left: 1px solid #b0b9c8; padding: 0;"></td>

                        <!-- BLOCK 3: CANCELLATION POLICY -->
                        <td style="vertical-align: top; width: 39%; text-align: center;">
                            <table align="center" style="border-collapse: collapse; margin-bottom: 8px;">
                                <tr>
                                    <td style="width: 70px; vertical-align: top;">
                                        <div
                                            style="background: #132358; width: 70px; height: 70px; border-radius: 42px; text-align: center; overflow: hidden;">
                                            <table
                                                style="width: 70px; height: 70px; border-collapse: collapse; margin: 0 auto;">
                                                <tr>
                                                    <td
                                                        style="vertical-align: middle; text-align: center; height: 70px; padding: 0; line-height: 1;">
                                                        <img src="{{ public_path('images/shield.svg') }}"
                                                            width="45"
                                                            style="vertical-align: middle; display: inline-block;">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                    <td style="padding-left: 12px; vertical-align: top; text-align: center;">
                                        <div
                                            style="font-family: 'Tajawal'; font-size: 12px; color: #132358; font-weight: 700; line-height: 1;">
                                            {{ $labels['cancellation_policy_ar'] ?? 'سياسة الإلغاء' }}</div>
                                        <div
                                            style="font-size: 12px; color: #132358; font-weight: 700; margin-top: 5px; line-height: 1;">
                                            CANCELLATION POLICY</div>
                                        <div
                                            style="margin-top: 7px; height: 2px; width: 160px; margin-left: auto; margin-right: auto;">
                                            <img src="{{ public_path('images/tapered_line.svg') }}"
                                                width="100%" height="2" style="display: block;">
                                        </div>
                                        <!-- Non-refundable content -->
                                        <!-- <div style="margin-top: 18px;">
                                    <div style="font-family: 'Tajawal'; font-size: 18px; color: #570519; font-weight: 700; line-height: 1;">الغاء غير مجاني</div>
                                    <div style="font-size: 17px; color: #570519; font-weight: 700; margin-top: 1px; line-height: 1;">Non-refundable</div>
                                </div> -->

                                        <!-- Cancellation refundable to content  -->
                                        <div style="margin-top: 8px;">
                                            <div
                                                style="font-family: 'Tajawal'; font-size: 13px; color: #000; font-weight: 400; line-height: 1;">
                                                {{ $labels['refundable_until_ar'] ?? 'الغاء مسترد حتى تاريخ' }}</div>
                                            <div
                                                style="font-size: 13px; color: #000; font-weight: 400; margin-top: 1px; line-height: 1; word-wrap: break-word; margin: 5px 0;">
                                                Cancellation refundable to</div>
                                            <div
                                                style="font-size: 17px; color: #132358; font-weight: 700; margin-top: 1px; line-height: 1;">
                                                {{ $cancellationDate ? strtoupper(\Carbon\Carbon::parse($cancellationDate)->format('d M Y')) : '-' }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>


                        </td>
                    </tr>
                </table>
            </div>

            <!-- ADDITIONAL INFORMATION & CONTACT SECTION -->
            <div
                style="border: 1px solid #b0b9c8; border-radius: 40px; padding: 10px 10px 20px 10px; background: #fff;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <!-- Section 1: Additional Information -->
                        <td style="vertical-align: top; width: 61%; padding: 0 5px;">
                            <div style="text-align: center; margin: 0 auto;">
                                <div
                                    style="font-family: 'Tajawal'; font-size: 10px; color: #132358; font-weight: 700; line-height: 1;">
                                    {{ $labels['additional_info_ar'] ?? 'معلومات اضافية' }}</div>
                                <div
                                    style="font-size: 10px; color: #132358; font-weight: 700; margin-top: 5px; line-height: 1;">
                                    Additional Information</div>
                                <div
                                    style="margin-top: 7px; height: 2px; width: 140px; margin-left: auto; margin-right: auto;">
                                    <img src="{{ public_path('images/tapered_line.svg') }}"
                                        width="100%" height="2" style="display: block;">
                                </div>
                            </div>
                            <div style="margin-top: 8px; font-size: 12px; color: #052757; line-height: 1.3; text-align: center;">
                                {{ $rateComments }}
                            </div>
                        </td>

                        <!-- Vertical Divider -->
                        <td style="width: 1px; border-left: 1px solid #b0b9c8; padding: 0;"></td>

                        <!-- Section 2: Contact Us -->
                        <td style="vertical-align: top; width: 39%; padding: 0 5px; text-align: center;">
                            <div
                                style="font-family: 'Tajawal'; font-size: 12px; color: #052757; font-weight: 500; line-height: 1; margin-top: 12px;">
                                {{ $labels['contact_help_ar'] ?? 'للمساعدة أثناء إقامتكم يرجى التواصل معنا.' }}</div>
                            <div
                                style="font-size: 12px; color: #052757; font-weight: 500; margin-top: 5px; line-height: 1;">
                                For assistance during your stay, </div>
                            <div
                                style="font-size: 12px; color: #052757; font-weight: 500; margin-top: 2px; line-height: 1;">
                                please feel free to contact us.</div>

                            <table align="center" style="margin-top: 15px; border-collapse: collapse;">
                                <tr>
                                    <td style="vertical-align: middle; padding-right: 15px;">
                                        <img src="{{ public_path('images/whatsapp.svg') }}"
                                            width="38">
                                    </td>
                                    <td style="vertical-align: middle; text-align: left;">
                                        <div
                                            style="font-size: 20px; color: #052757; font-weight: 700; line-height: 1;">
                                            +966 5 6644 2131</div>
                                        <div
                                            style="font-size: 15px; color: #052757; font-weight: 400; margin-top: 2px; line-height: 1;">
                                            info@travelregions.sa</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <!-- FOOTER -->
        <div
            style="margin-top: 20px; text-align: center; background: #042657; color: #fff; padding: 11px 10px 18px 10px;">
            <div style="font-family: 'Tajawal'; font-size: 13px; font-weight: 500; color: #fff; line-height: 1;">
                {{ $labels['footer_wish_ar'] ?? 'نتمنى لكم اقامة ممتعة و رحلة سعيدة' }}</div>
            <div style="font-size: 12px; font-weight:400; color: #fff; line-height: 1; margin-top: 5px;">
                WE WISH YOU A PLEASANT STAY AND A HAPPY JOURNEY
            </div>
        </div>
    </div>
</body>

</html>
