<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Xint0\BanxicoPHP\SieClient;

class ExchangeRateService
{
    public function latestUsdMxn(): array
    {
        return Cache::store('redis')->remember(
            'banxico:fix:usd-mxn',
            (int) config('services.banxico.cache_ttl', 1800),
            fn (): array => $this->fetchLatestUsdMxn()
        );
    }

    private function fetchLatestUsdMxn(): array
    {
        $token = config('services.banxico.token');

        if (! is_string($token) || trim($token) === '') {
            throw new RuntimeException(
                'BANXICO_TOKEN no está configurado.'
            );
        }

        $client = new SieClient($token);

        // Consultamos un rango para conservar las fechas.
        $endDate = CarbonImmutable::today('America/Mexico_City');
        $startDate = $endDate->subDays(14);

        $observations = $client->exchangeRateUsdDetermination(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        if (! is_array($observations) || $observations === []) {
            throw new RuntimeException(
                'Banxico no devolvió cotizaciones fechadas.'
            );
        }

        ksort($observations);

        $publishedDate = array_key_last($observations);
        $rate = $observations[$publishedDate];

        if (
            ! is_string($publishedDate) ||
            ! is_scalar($rate) ||
            ! preg_match('/^\d+(?:\.\d+)?$/', (string) $rate) ||
            ! preg_match('/[1-9]/', (string) $rate)
        ) {
            throw new RuntimeException(
                'Banxico devolvió una cotización inválida.'
            );
        }

        return [
            'from' => 'USD',
            'to' => 'MXN',
            'rate' => (string) $rate,
            'published_date' => $publishedDate,
            'source' => 'BANXICO_FIX',
            'series' => 'SF43718',
        ];
    }

    public function clearLatestCache(): void
    {
        Cache::store('redis')->forget('banxico:fix:usd-mxn');
    }
}
