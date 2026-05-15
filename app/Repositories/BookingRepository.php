<?php

namespace App\Repositories;

use App\Http\Resources\BookingResource;
use App\Interfaces\BookingInterface;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\BookingRoom;
use App\Models\BookingRoomCancellationPolicy;
use App\Models\Coupon;
use App\Traits\HotelBedsTrait;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingRepository implements BookingInterface
{
    use HotelBedsTrait;

    /**
     * Display a listing of the bookings for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($request)
    {
        $user = $request->user();
        $bookings = $user->bookings()->when(function ($query) {
            // Add any filtering logic here if needed
            if ($status = request('status')) {
                $query->where('status', $status);
            }
            $query->where('status', '!=', 'pending');
            if ($hotelCode = request('hotel_code')) {
                $query->where('hotel_code', $hotelCode);
            }
        })->with('details')->latest()->paginate();

        return $bookings;
    }

    /**
     * Display the specified booking.
     *
     * @param int $order
     * @return Booking
     */
    public function show($order)
    {
        $booking = Booking::where('order', $order)
            ->where('user_id', Auth::id())
            ->with('details', 'booking_room')
            ->firstOrFail();

        return $booking;
    }

    /**
     * Store a newly created booking in storage.
     *
     * @param object $request
     * @return Booking $booking
     */
    public function store($request)
    {
        // check room available or not
        try {
            $hotel_details = $this->getHotelDetails($request, $request->hotel_code);

            $address = $hotel_details['hotel']['address']['street'] . ', ' . $hotel_details['hotel']['city']['content'] . ', ' . ($hotel_details['hotel']['postalCode'] ?? '');

            $booking = Booking::updateOrCreate([
                'user_id' => Auth::id(),
                'hotel_code' => $request->hotel_code,
                'status' => 'pending',
            ], [
                'accommodation_type' => $hotel_details['hotel']['accommodationType']['typeDescription'] ?? null,
                'address' => $address,
                'phone' => json_encode($hotel_details['hotel']['phones']),
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'hotel_name' => $request->hotel_name,
                'hotel_location' => $request->hotel_location,
                'hotel_images' => $request->hotel_images,
                'rooms' => $request->rooms,
                'adults' => $request->adults,
                'children' => $request->children ?? 0,
                'child_age' => $request->child_age_data ? json_encode($request->child_age_data) : null,
                'nights' => $request->nights,
                'total_price' => $request->total_price,
                'currency' => $request->currency,
                'special_requests' => $request->special_requests,
            ]);

            $booking->update([
                'order' => 'TR_' . Carbon::now()->format('Y') . $booking->id,
            ]);

            $booking->details()->delete();
            foreach ($request->details as $detail) {
                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'room_code' => $detail['room_code'] ?? null,
                    'price_per_night' => $detail['price_per_night'],
                    'first_name' => $detail['first_name'],
                    'last_name' => $detail['last_name'],
                    'email' => $detail['email'],
                    'country' => $detail['country'],
                    'country_code' => $detail['country_code'],
                    'phone' => $detail['phone'],
                    'is_primary' => $detail['is_primary'] ? 1 : 0,
                ]);
            }

            $booking->booking_room()->delete();
            foreach ($request->room_details as $room) {
                BookingRoom::create([
                    'booking_id' => $booking->id,
                    'room_code' => $room['room_code'] ?? '',
                    'rate_key' => $room['rate_key'],
                    'room_name' => $room['room_name'] ?? null,
                    'board_name' => $room['board_name'] ?? null,
                ]);
            }

            $room_rates = [];
            foreach ($request->room_details as $room_details) {
                $room_rates[] = $room_details['rate_key'];
            }
            $roomAvailability = $this->checkRoomAvailability($room_rates);

            foreach ($roomAvailability['hotel']['rooms'] as $room) {
                foreach ($room['rates'] as $rate) {
                    $bookingRoom = BookingRoom::where('booking_id', $booking->id)
                        ->where('rate_key', $rate['rateKey'])
                        ->first();

                    $roomPrices = $this->calculatePrice($rate['net'], $roomAvailability['hotel']['categoryName'], $roomAvailability['hotel']['currency'], $roomAvailability['hotel']['code'], $roomAvailability['hotel']['destinationName']);

                    $bookingRoom->update([
                        'amount' => $roomPrices['final_amount'],
                        'rate_class' => $rate['rateClass'] ?? null,
                        'net_amount' => $rate['net'],
                        'net_currency' => $roomAvailability['hotel']['currency'],
                        'rate_comments' => $rate['rateComments'] ?? null,
                    ]);

                    foreach ($rate['cancellationPolicies'] as $policy) {
                        if ($bookingRoom) {
                            BookingRoomCancellationPolicy::updateOrCreate([
                                'booking_room_id' => $bookingRoom->id,
                                'amount' => $policy['amount'],
                                'from' => $policy['from'],
                            ]);
                        }
                    }
                }
            }

            $prices = $this->calculatePrice($roomAvailability['hotel']['totalNet'], $roomAvailability['hotel']['categoryName'], $roomAvailability['hotel']['currency'], $roomAvailability['hotel']['code'], $roomAvailability['hotel']['destinationName']);

            $booking->update([
                'total_price' => $prices['final_amount'],
                'currency' => $prices['converted_currency'],
                'category' => $roomAvailability['hotel']['categoryName'],
                'net_total_price' => $roomAvailability['hotel']['totalNet'],
                'net_currency' => $roomAvailability['hotel']['currency'],
            ]);
        } catch (Exception $e) {
            Log::error('Room Availability Check Failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw new Exception($e->getMessage());
        }

        return $booking;
    }

    /**
     * Apply a coupon to the booking.
     *
     * @param Request $request
     * @return array
     */
    public function applyCoupon($request)
    {
        $coupon = Coupon::where('code', $request->coupon_code)
            ->first();

        if (!$coupon) {
            return [
                'status' => false,
                'message' => __('messages.coupon.invalid'),
            ];
        }

        $booking = Booking::where('order', $request->order)
            ->firstOrFail();

        $discount_amount = 0;
        if ($coupon->type === 'percentage') {
            $discount_amount = ($coupon->discount / 100) * $booking->total_price;
        } elseif ($coupon->type === 'fixed') {
            $discount_amount = $coupon->discount;
        }

        $booking->update([
            'coupon_id' => $coupon->id,
            'discount_amount' => $discount_amount,
        ]);

        return [
            'status' => true,
            'message' => __('messages.coupon.valid'),
            'data' => [
                'booking' => new BookingResource($booking->fresh()),
            ],
        ];
    }

    public function downloadPdf($order, $language = 'en')
    {
        $booking = Booking::where('order', $order)->firstOrFail();
        $labels = [];
        $statusMapAr = [];

        // File name & full path
        $fileName = 'booking-confirmation-' . $booking->id . '.pdf';
        $filePath = public_path('booking-pdfs/' . $fileName);

        // Create folder if not exists
        if (!file_exists(public_path('booking-pdfs'))) {
            mkdir(public_path('booking-pdfs'), 0777, true);
        }

        if ($booking->booking_reference) {
            $this->bookingReconfirmation($booking->booking_reference);
        }

        // Shape all dynamic Arabic fields from DB.
        // Voucher is bilingual, so this should run regardless of language flag.
        $booking->hotel_name         = $this->shapeArabic($booking->hotel_name);
        $booking->address            = $this->shapeArabic($booking->address);
        $booking->accommodation_type = $this->shapeArabic($booking->accommodation_type);
        $booking->supplier_name      = $this->shapeArabic($booking->supplier_name);
        $booking->special_requests   = $this->shapeArabic($booking->special_requests);

        if ($booking->primary_details) {
            $booking->primary_details->first_name = $this->shapeArabic($booking->primary_details->first_name);
            $booking->primary_details->last_name = $this->shapeArabic($booking->primary_details->last_name);
        }

        foreach ($booking->booking_room as $room) {
            $room->room_name     = $this->shapeArabic($room->room_name);
            $room->board_name    = $this->shapeArabic($room->board_name);
            $room->rate_comments = $this->shapeArabic($room->rate_comments);
        }

        $labels = [
            'voucher_title_ar' => 'قسيمة حجز فندق',
            'hotel_information_ar' => 'معلومات الفندق',
            'booking_status_ar' => 'حالة الحجز',
            'lead_guest_ar' => 'اسم النزيل الرئيس',
            'stay_details_ar' => 'تفاصيل الاقامة',
            'check_in_ar' => 'تاريخ الوصول',
            'check_out_ar' => 'تاريخ المغادرة',
            'rooms_count_ar' => 'عدد الغرف',
            'room_ar' => 'غرفة',
            'services_ar' => 'الخدمات',
            'breakfast_included_ar' => 'شامل الافطار',
            'free_wifi_ar' => 'انترنت مجاني',
            'room_details_ar' => 'تفاصيل الغرفة',
            'room_type_ar' => 'نوع الغرفة',
            'occupancy_ar' => 'عدد الضيوف',
            'adult_ar' => 'بالغ',
            'child_ar' => 'طفل',
            'years_ar' => 'سنوات',
            'cancellation_policy_ar' => 'سياسة الإلغاء',
            'refundable_until_ar' => 'الغاء مسترد حتى تاريخ',
            'additional_info_ar' => 'معلومات اضافية',
            'contact_help_ar' => 'للمساعدة أثناء إقامتكم يرجى التواصل معنا.',
            'footer_wish_ar' => 'نتمنى لكم اقامة ممتعة و رحلة سعيدة',
            'status_confirmed' => 'مؤكد',
            'status_cancelled' => 'ملغي',
            'status_pending' => 'قيد الانتظار',
        ];

        $labels = array_map(fn($label) => $this->shapeArabic($label), $labels);

        $statusMapAr = [
            'confirmed' => $labels['status_confirmed'],
            'cancelled' => $labels['status_cancelled'],
            'pending' => $labels['status_pending'],
        ];

        $selectedFacilities = [
            'Internet access',
            'Minibar',
            'Restaurant',
            'Wheelchair-accessible',
            '24-hour reception',
            'Outdoor swimming pool',
            'Gym',
            'Valet parking',
            'Spa centre',
            'Bathroom',
            'Shower',
            'Hot tub',
            'Private Pool',
            'TV',
            'Connecting rooms',
        ];

        $facilityRows = DB::table('hotel_facilities as hf')
            ->join('facilities as f', 'f.code', '=', 'hf.facility_code')
            ->where('hf.hotel_code', $booking->hotel_code)
            ->whereNotNull('f.name')
            ->whereIn('f.name', $selectedFacilities)
            ->orderBy('hf.id')
            ->limit(2)
            ->get(['f.name']);

        $services = $facilityRows->values()->map(function ($facility, $index) {
            $serviceName = trim((string) ($facility->name ?? ''));

            return [
                'en' => $serviceName,
            ];
        })->all();

        if (count($services) < 2) {
            $fallbackServices = [
                [
                    'en' => 'Breakfast',
                ],
                [
                    'en' => 'Free WiFi',
                ],
            ];

            $services = array_slice(array_merge($services, array_slice($fallbackServices, count($services))), 0, 2);
        }

        $data = [
            'booking' => $booking,
            'labels' => $labels,
            'statusMapAr' => $statusMapAr,
            'services' => $services,
        ];

        $view = 'pdf.booking-voucher';

        $pdf = Pdf::loadView($view, $data)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,   // ← allows local file:// font loading
                'defaultFont'          => 'Montserrat',
                'chroot'               => public_path(), // ← lets DomPDF access public/
                'isFontSubsettingEnabled' => true,
            ])
            ->setPaper('A4', 'portrait');

        $pdf->save($filePath);

        return [
            'status'  => true,
            'message' => __('messages.booking.pdf.generated'),
            'data'    => [
                'pdf_url' => url('booking-pdfs/' . $fileName),
            ],
        ];
    }

    private function shapeArabic($text): string
    {
        if (empty($text)) return (string) $text;

        $arabic = new Arabic();
        $p = $arabic->arIdentify($text);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($text, $p[$i-1], $p[$i] - $p[$i-1]));
            $text = substr_replace($text, $utf8ar, $p[$i-1], $p[$i] - $p[$i-1]);
        }

        return $text;
    }

    public function showCancellationPolicies($order)
    {
        $booking = Booking::where('order', $order)->firstOrFail();

        $bookingRoomCancellationPolicy = BookingRoomCancellationPolicy::whereIn('booking_room_id', function ($query) use ($booking) {
            $query->select('id')
                ->from('booking_rooms')
                ->where('rate_class', '!=', 'NRF')
                ->where('booking_id', $booking->id);
        })->get();

        return [
            'status' => true,
            'message' => __('messages.booking.cancellation-policies-fetched'),
            'data' => [
                'cancellation_policies' => $bookingRoomCancellationPolicy,
            ],
        ];
    }
}
