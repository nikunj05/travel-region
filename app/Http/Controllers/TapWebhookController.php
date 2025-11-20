<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TapWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $order = $request->reference ? $request->reference['order'] : null;
        $status = $request->status;

        if ($order && $status == 'AUTHORIZED') {
            Booking::where('order', $order)
                ->update([
                    'status' => 'confirmed',
                    'tap_response' => json_encode($request->all()),
                ]);

            return response()->json(['status' => 'success'], 200);
        }

        Log::warning('Tap Webhook: Invalid data received', $request->all());

        return response()->json(['status' => 'error'], 400);
    }
}
