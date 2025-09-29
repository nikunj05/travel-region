<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TapPaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'booking_id' => 'required|integer|exists:bookings,id',
        ]);

        $paymentId = uniqid();

        $booking = Booking::findOrFail($request->booking_id);

        $payload = [
            "amount" => $request->amount,
            "currency" => $request->currency,
            "customer_initiated" => true,
            "threeDSecure" => true,
            "save_card" => false,
            "receipt" => [
                "email" => true,
                "sms" => true,
            ],
            "metadata" => [
                "booking_id" => $booking->id,
                "hotel_code" => $booking->hotel_code,
            ],
            "reference" => [
                "transaction" => "txn_" . $paymentId,
                "order" => "ord_" . $paymentId,
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
                "url" => route('tap.webhook'),
            ],
        ];

        try {
            $response = Http::withToken(env('TAP_SECRET'))
                ->acceptJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.tap.company/v2/authorize/', $payload);

            if ($response->successful()) {

                Order::create([
                    'user_id' => Auth::id(),
                    'booking_id' => $booking->id,
                    'amount' => $request->amount,
                    'currency' => $request->currency,
                    'status' => 'pending',
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
