<?php

namespace App\Traits;

use App\Models\CommissionByCity;
use App\Models\MarkupHotel;
use App\Models\RateExchange;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
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
        $cacheKey = 'exchange_rate_' . $fromCurrency . '_' . $toCurrency . '_' . $today->toDateString();

        // Cache for 6 hours
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($fromCurrency, $toCurrency, $today) {
            $exchangeRate = RateExchange::where('from_currency', $fromCurrency)
                ->where('to_currency', $toCurrency)
                // ->where('rate_date', $today)
                ->first();

            return $exchangeRate;
        });
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

    /**
     * Get commission rate based on hotel category
     *
     * @param string $category
     * @return float
     */
    public function getCommissionRate($category)
    {
        $setting = Setting::first();

        if (!$setting) {
            return 0; // Default to 0% if settings are not found
        }

        switch ($category) {
            case '5 STARS':
                $commission_percentage = $setting->five_star_commission;
                break;
            case 'خمسه نجوم':
                $commission_percentage = $setting->five_star_commission;
                break;
            case '4 STARS':
                $commission_percentage = $setting->four_star_commission;
                break;
            case 'اربعه نجوم':
                $commission_percentage = $setting->four_star_commission;
                break;
            case '3 STARS':
                $commission_percentage = $setting->three_star_commission;
                break;
            case 'ثلاثه نجوم':
                $commission_percentage = $setting->three_star_commission;
                break;
            case '2 STARS':
                $commission_percentage = $setting->two_star_commission;
                break;
            case 'نجمتان':
                $commission_percentage = $setting->two_star_commission;
                break;
            case '1 STAR':
                $commission_percentage = $setting->one_star_commission;
                break;
            case 'نجمه':
                $commission_percentage = $setting->one_star_commission;
                break;
            default:
                $commission_percentage = 0;
        }

        return $commission_percentage;
    }

    /**
     * Calculate final price after conversion, commission, and taxes
     *
     * @param float $amount
     * @param string $category
     * @param string $fromCurrency
     * @param string|null $hotel_code
     * @param string|null $city
     * @return array
     */
    public function calculatePrice($amount, $category, $fromCurrency, $hotel_code = null, $city = null)
    {
        $expectedCurrency = 'SAR';

        try {
            $exchangeRate = $this->getUpdatedExchangeRates($fromCurrency, $expectedCurrency);
        } catch (Exception $e) {
            $exchangeRate = 1; // Fallback to 1 if conversion fails
            $expectedCurrency = $fromCurrency;
        }

        $commission_percentage = $this->getCommissionRate($category);

        $converted_amount = $amount * $exchangeRate;

        // get markup percentage for the hotel
        if ($hotel_code) {
            $markup_percentage = MarkupHotel::where('hotel_code', $hotel_code)->first();

            if ($markup_percentage) {
                $commission_percentage += $markup_percentage->markup_percentage;
            }
        }

        // Calculate commission by city
        if ($city) {
            $commission_by_city = CommissionByCity::where('city', $city)->first();

            if ($commission_by_city) {
                $commission_percentage += $commission_by_city->commission_percentage;
            }
        }

        $commission_amount = ($commission_percentage / 100) * $converted_amount;

        return [
            'original_amount' => $amount,
            'original_currency' => $fromCurrency,
            'converted_currency' => $expectedCurrency,
            'commission_percentage' => $commission_percentage,
            'converted_amount' => $converted_amount,
            'commission_amount' => $commission_amount,
            'final_amount' => round($converted_amount + $commission_amount)
        ];
    }
}
