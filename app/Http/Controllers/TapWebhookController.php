<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TapWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Tap Webhook Received:', $request->all());

        return response()->json(['status' => 'success'], 200);
    }
}
