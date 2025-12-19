<?php

namespace App\Http\Controllers;

use App\Http\Requests\TapPaymentRequest;
use App\Models\Booking;
use App\Traits\HotelBedsTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TapPaymentController extends Controller
{
    use HotelBedsTrait;

    public function checkout(TapPaymentRequest $request)
    {
        $existingBooking = Booking::where('order', $request->order)
            ->where('status', 'pending')
            ->first();
        if (empty($existingBooking)) {
            return $this->sendApiResponse(false, __('messages.payment.already_paid'), [], 422);
        }

        $payload = [
            "amount" => $existingBooking->total_price - $existingBooking->discount_amount,
            "currency" => $existingBooking->currency,
            "customer_initiated" => true,
            "threeDSecure" => true,
            "save_card" => false,
            "receipt" => [
                "email" => true,
                "sms" => true,
            ],
            "metadata" => [
                "booking_id" => $existingBooking->id,
                "hotel_code" => $existingBooking->hotel_code,
            ],
            "reference" => [
                "transaction" => "txn_" . $existingBooking->order,
                "order" => $existingBooking->order,
            ],
            "customer" => [
                "first_name" => Auth::user()->first_name,
                "last_name" => Auth::user()->last_name,
                "email" => Auth::user()->email,
                "phone" => [
                    "country_code" => Auth::user()->country_code,
                    "number" => Auth::user()->mobile,
                ],
            ],
            "source" => [
                "id" => "src_card",
            ],
            "post" => [
                "url" => route('tap.webhook'),
            ],
            "redirect" => [
                "url" => env('FRONTEND_URL') . '/en/booking-confirmation/' . $existingBooking->order,
            ],
        ];

        try {
            $response = Http::withToken(env('TAP_SECRET'))
                ->acceptJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.tap.company/v2/charges/', $payload);

            if ($response->successful()) {

                $existingBooking->update([
                    'tap_response' => $response->body(),
                ]);

                return $this->sendApiResponse(true, __('messages.payment.checkout_initiated'), [
                    'checkout' => $response->json(),
                ]);
            }

            return $this->sendApiResponse(false, __('messages.payment.checkout_failed'), [
                'checkout' => $response->json(),
            ], 422);

        } catch (\Throwable $e) {
            return $this->sendApiResponse(false, $e->getMessage(), [], 500);
        }
    }
}
