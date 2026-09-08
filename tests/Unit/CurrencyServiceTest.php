<?php

namespace Tests\Unit;

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Class CurrencyServiceTest
 * 
 * Unit tests for CurrencyService: API fetching, caching, cross-rate calculations, and error fallback logic.
 */
class CurrencyServiceTest extends TestCase
{
    protected CurrencyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['services.openexchangerates.api_key' => 'test_api_key']);
        $this->service = new CurrencyService();
    }

    /**
     * Test that exchange rates are fetched from HTTP API and stored in cache.
     */
    public function test_it_fetches_and_caches_exchange_rates(): void
    {
        Http::fake([
            '*' => Http::response([
                'rates' => [
                    'USD' => 1.0,
                    'EUR' => 0.90,
                    'GBP' => 0.75,
                ],
            ], 200),
        ]);

        $rates = $this->service->getRates();

        $this->assertEquals(0.90, $rates['EUR']);
        $this->assertEquals(0.75, $rates['GBP']);
        $this->assertTrue(Cache::has('openexchangerates_rates'));
    }

    /**
     * Test that cross-rate calculations to EUR are computed correctly.
     */
    public function test_it_calculates_cross_rates_correctly(): void
    {
        Http::fake([
            '*' => Http::response([
                'rates' => [
                    'USD' => 1.0,
                    'EUR' => 0.90,
                    'GBP' => 0.75,
                ],
            ], 200),
        ]);

        // EUR to EUR -> 100
        $this->assertEquals(100.00, $this->service->convertToEur(100, 'EUR'));

        // USD to EUR -> 100 * 0.90 = 90.00
        $this->assertEquals(90.00, $this->service->convertToEur(100, 'USD'));

        // GBP to EUR -> 100 * (0.90 / 0.75) = 120.00
        $this->assertEquals(120.00, $this->service->convertToEur(100, 'GBP'));
    }

    /**
     * Test that fallback rates are used when API request fails.
     */
    public function test_it_uses_fallback_rates_on_api_failure(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $rates = $this->service->getRates();

        $this->assertArrayHasKey('EUR', $rates);
        $this->assertEquals(0.90, $rates['EUR']);
        $this->assertEquals(0.75, $rates['GBP']);
    }
}
