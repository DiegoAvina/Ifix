<?php

namespace Tests\Feature;

use App\Services\Lunar\SafeTelemetryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lunar\Base\ProvidesTelemetryInsights;
use Lunar\Base\TelemetryServiceInterface;
use Tests\TestCase;

class LunarTelemetryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        Cache::flush();
    }

    public function test_invalid_cached_telemetry_value_is_discarded(): void
    {
        $telemetry = new SafeTelemetryService($this->insights());

        Cache::put($telemetry->getCacheKey(), new \stdClass(), 86400);

        $this->assertTrue($telemetry->shouldRun());
        $this->assertNull(Cache::get($telemetry->getCacheKey()));
    }

    public function test_successful_telemetry_attempt_is_cached_as_a_string(): void
    {
        $this->app->instance(ProvidesTelemetryInsights::class, $this->insights());

        Http::fake([
            'https://stats.lunarphp.io/api/insights' => Http::response([], 200),
        ]);

        $telemetry = app(TelemetryServiceInterface::class);

        $this->assertInstanceOf(SafeTelemetryService::class, $telemetry);

        $telemetry->run();
        $telemetry->run();

        $this->assertIsString(Cache::get($telemetry->getCacheKey()));
        Http::assertSentCount(1);
    }

    private function insights(): ProvidesTelemetryInsights
    {
        $insights = \Mockery::mock(ProvidesTelemetryInsights::class);

        $insights->shouldReceive([
            'domainHash' => 'test-domain',
            'environment' => 'testing',
            'laravelVersion' => '12',
            'lunarVersion' => '1',
            'dbDriver' => 'sqlite',
            'phpVersion' => '8.3',
            'productCount' => 0,
            'productVariantCount' => 0,
            'orderCount' => 0,
            'orderTotal' => 0,
            'currencies' => collect(),
            'languages' => collect(),
        ]);

        return $insights;
    }
}
