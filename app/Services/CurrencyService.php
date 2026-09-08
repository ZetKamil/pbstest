<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class CurrencyService
 * 
 * Handles currency exchange rate retrieval and conversion to EUR.
 * Integrates with OpenExchangeRates API, implements a 1-hour cache layer,
 * provides automatic fallback rates in case of API failure, and calculates
 * cross-rates for non-USD currencies (due to OpenExchangeRates free plan constraints).
 */
class CurrencyService
{
    /**
     * Unique cache key used to store exchange rates in Laravel's cache store.
     */
    protected const CACHE_KEY = 'openexchangerates_rates';

    /**
     * Cache duration in seconds (3600 seconds = 1 hour).
     * 
     * WHY CACHING IS ESSENTIAL:
     * 1. Rate Limiting: The OpenExchangeRates free tier limits accounts to 1,000 API requests per month.
     *    Fetching rates on every HTTP request would quickly exhaust the quota.
     * 2. Performance: Avoids network latency by serving rates instantly from memory/cache (ms vs ~300ms+ HTTP call).
     * 3. Stability: Reduces reliance on external service availability for every user interaction.
     */
    protected const CACHE_TTL = 3600;

    /**
     * Default fallback exchange rates (relative to USD as base currency).
     * 
     * WHY FALLBACK RATES ARE USED:
     * - Implements the "Graceful Degradation" pattern.
     * - If the API key is missing, invalid, rate-limited, or if OpenExchangeRates API is down,
     *   the application continues to function smoothly without throwing 500 HTTP Server Errors.
     * - Rates represent realistic base conversion approximations (USD = 1.0, EUR ~ 0.90, GBP ~ 0.75).
     *
     * @var array<string, float>
     */
    protected array $fallbackRates = [
        'USD' => 1.0,
        'EUR' => 0.90,
        'GBP' => 0.75,
    ];

    /**
     * Retrieve exchange rates from OpenExchangeRates API with 1-hour caching and fallback handling.
     *
     * @return array<string, float> Dictionary of currency codes mapped to their exchange rate relative to USD.
     */
    public function getRates(): array
    {
        // Cache::remember checks if 'openexchangerates_rates' exists in cache.
        // If present, it returns cached rates immediately.
        // If absent or expired, it executes the callback, stores the result for 3600s, and returns it.
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $apiKey = config('services.openexchangerates.api_key');
            $baseUrl = config('services.openexchangerates.url');

            // 1. Check if API key is provided and not set to placeholder value
            if (empty($apiKey) || $apiKey === 'your_api_key_here') {
                Log::warning('CurrencyService: OpenExchangeRates API key is missing or set to placeholder. Using fallback exchange rates.');
                return $this->fallbackRates;
            }

            try {
                // 2. Perform HTTP GET request with a 5-second timeout and SSL fallback for local dev environments
                $response = Http::withoutVerifying()->timeout(5)->get($baseUrl, [
                    'app_id' => $apiKey,
                ]);

                // 3. Verify HTTP 200 OK status and presence of 'rates' payload
                if ($response->successful() && isset($response->json()['rates'])) {
                    /** @var array<string, float> $rates */
                    $rates = $response->json()['rates'];
                    return $rates;
                }

                // Log detailed error information if API returned a non-200 response (e.g., 401 Unauthorized, 429 Too Many Requests)
                Log::error('CurrencyService: OpenExchangeRates API call failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (Throwable $e) {
                // Catch network exceptions (connection timeout, DNS failure, connection refused)
                Log::error('CurrencyService: Exception occurred while contacting OpenExchangeRates API.', [
                    'message' => $e->getMessage(),
                ]);
            }

            // Return safe fallback rates if any step failed
            return $this->fallbackRates;
        });
    }

    /**
     * Convert an amount in a given currency to EUR using USD cross-rate math.
     *
     * MATHEMATICAL LOGIC & CROSS-RATES EXPLAINED:
     * ---------------------------------------------------------------------------------------
     * The free plan of OpenExchangeRates ONLY provides rates relative to USD ($1.00 USD).
     * The API response format gives rates as: 1 USD = X Currency.
     * Example rates: 1 USD = 0.92 EUR | 1 USD = 0.79 GBP
     *
     * Case 1: From EUR to EUR
     * - Target is EUR, source is EUR -> No conversion required (1:1 ratio).
     *   Formula: $amount
     *
     * Case 2: From USD to EUR
     * - Since rates are based on USD (1 USD = rate['EUR'] EUR), we directly multiply:
     *   Formula: $amount * rate['EUR']
     *   Example: $100.00 USD * 0.92 = €92.00 EUR.
     *
     * Case 3: From GBP (or any non-USD currency) to EUR (Cross-Rate Triangulation)
     * - We convert GBP to USD first: Amount_in_USD = $amount / rate['GBP']
     * - Then convert USD to EUR:     Amount_in_EUR = Amount_in_USD * rate['EUR']
     * - Combined cross-rate formula: Amount_in_EUR = $amount * (rate['EUR'] / rate['GBP'])
     *   Example: £100.00 GBP * (0.92 / 0.79) = £100.00 * 1.1645 = €116.46 EUR.
     * ---------------------------------------------------------------------------------------
     *
     * @param float $amount The financial amount in the user's local currency.
     * @param string $fromCurrency The 3-character ISO currency code (e.g., 'EUR', 'USD', 'GBP').
     * @return float The converted amount in EUR, rounded to 2 decimal places.
     */
    public function convertToEur(float $amount, string $fromCurrency): float
    {
        // Normalize currency string to uppercase (e.g. 'eur' -> 'EUR')
        $currency = strtoupper(trim($fromCurrency));

        // Case 1: Source currency is already EUR - return original amount
        if ($currency === 'EUR') {
            return round($amount, 2);
        }

        // Fetch current exchange rates (cached or live)
        $rates = $this->getRates();
        $eurRate = (float) ($rates['EUR'] ?? $this->fallbackRates['EUR']);

        // Case 2: Source currency is USD -> Direct multiplication with EUR rate
        if ($currency === 'USD') {
            return round($amount * $eurRate, 2);
        }

        // Case 3: Source currency is GBP -> Cross-rate calculation (EUR_rate / GBP_rate)
        if ($currency === 'GBP') {
            $gbpRate = (float) ($rates['GBP'] ?? $this->fallbackRates['GBP']);

            // Safety check against division by zero
            if ($gbpRate <= 0) {
                Log::warning("CurrencyService: Invalid GBP rate ({$gbpRate}). Falling back to direct EUR rate.");
                return round($amount * $eurRate, 2);
            }

            return round($amount * ($eurRate / $gbpRate), 2);
        }

        // Generic fallback for any other supported ISO currency (e.g., JPY, CAD)
        if (isset($rates[$currency]) && (float) $rates[$currency] > 0) {
            $fromRate = (float) $rates[$currency];
            return round(($amount / $fromRate) * $eurRate, 2);
        }

        // Default fallback if currency code is unrecognised
        return round($amount, 2);
    }

    /**
     * Format a numerical amount into a readable currency string with currency symbol.
     *
     * @param float $amount The numerical value to format.
     * @param string $currency The 3-letter ISO code ('EUR', 'USD', 'GBP').
     * @return string Formatted string (e.g. "€150.00", "$300.00", "£450.00").
     */
    public function formatCurrency(float $amount, string $currency): string
    {
        $formatted = number_format($amount, 2, '.', ',');

        return match (strtoupper(trim($currency))) {
            'EUR' => "€{$formatted}",
            'USD' => "\${$formatted}",
            'GBP' => "£{$formatted}",
            default => "{$formatted} " . strtoupper(trim($currency)),
        };
    }
}
