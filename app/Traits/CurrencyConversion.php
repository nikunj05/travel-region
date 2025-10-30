<?php

namespace App\Traits;

use App\Models\RateExchange;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait CurrencyConversion
{
    /**
     * Get exchange rate from database
     */
    public function getExchangeRate($fromCurrency, $toCurrency)
    {
        $today = Carbon::today();

        $exchangeRate = RateExchange::where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency)
            ->where('rate_date', $today)
            ->first();

        return $exchangeRate ? $exchangeRate->rate : null;
    }

    /**
     * Fetch and store exchange rates (called by scheduled task)
     */
    public function getUpdatedExchangeRates($fromCurrency, $toCurrency)
    {
        $apiKey = env('EXCHANGE_RATE_API_KEY');
        $exchangeRate = $this->getExchangeRate($fromCurrency, $toCurrency);

        if ($exchangeRate) {
            return $exchangeRate->exchange_rate; // Rates are up to date
        }

        try {
            $response = Http::timeout(10)->get(
                "https://v6.exchangerate-api.com/v6/{$apiKey}/pair/{$fromCurrency}/{$toCurrency}"
            );

            if ($response->successful()) {
                $data = $response->json();

                $exchangeRate = RateExchange::updateOrCreate(
                    [
                        'from_currency' => $fromCurrency,
                        'to_currency' => $toCurrency,
                        'rate_date' => Carbon::today()
                    ],
                    [
                        'exchange_rate' => $data['conversion_rate']
                    ]
                );

                return $exchangeRate->exchange_rate;
            } else {
                Log::error("Failed to fetch exchange rate: " . $response->body());
                throw new Exception("Failed to fetch exchange rate from API");
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return true;
    }
}
