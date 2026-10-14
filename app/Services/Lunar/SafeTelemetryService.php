<?php

namespace App\Services\Lunar;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lunar\Base\TelemetryService;

class SafeTelemetryService extends TelemetryService
{
    public function shouldRun(): bool
    {
        if (! $this->shouldRun) {
            return false;
        }

        $lastAttempt = Cache::get($this->getCacheKey());

        if (! $lastAttempt) {
            return true;
        }

        if (! is_string($lastAttempt) && ! is_int($lastAttempt) && ! is_float($lastAttempt)) {
            Cache::forget($this->getCacheKey());

            return true;
        }

        return ! now()->parse($lastAttempt)->isToday();
    }

    public function run(): void
    {
        if (! $this->shouldRun()) {
            return;
        }

        Cache::forget($this->getCacheKey());

        Cache::remember($this->getCacheKey(), 86400, function (): ?string {
            $response = Http::withHeader('Accept', 'application/json')
                ->timeout(3)
                ->retry(3, 100)
                ->post($this->getInsightsUrl(), $this->getInsightsPayload());

            return $response->successful() ? now()->toIso8601String() : null;
        });
    }
}
