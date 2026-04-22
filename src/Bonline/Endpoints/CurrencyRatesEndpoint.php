<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Endpoints;

use GiorgiGrdzelidze\BogSdk\Bonline\Dto\CurrencyRateDto;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Bonline endpoint for BOG commercial and NBG currency exchange rates.
 */
final class CurrencyRatesEndpoint
{
    private const DEFAULT_CURRENCIES = ['USD', 'EUR', 'GBP'];

    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Get commercial exchange rates for common currencies.
     *
     * The BOG API only supports per-currency lookups, so this fetches
     * rates for USD, EUR, GBP (or a custom list) and returns them all.
     *
     * @param  string[]  $currencies  ISO 4217 codes to fetch (defaults to USD, EUR, GBP).
     * @return CurrencyRateDto[]
     */
    public function list(array $currencies = self::DEFAULT_CURRENCIES): array
    {
        return array_map(
            fn (string $currency): CurrencyRateDto => $this->commercial($currency),
            $currencies,
        );
    }

    /**
     * Get the BOG commercial exchange rate for a currency against GEL.
     *
     * @param  string  $currency  ISO 4217 currency code (e.g. USD, EUR).
     */
    public function commercial(string $currency): CurrencyRateDto
    {
        $data = $this->http->get(
            'bonline',
            $this->baseUrl.'/rates/commercial/'.urlencode($currency),
        );

        return CurrencyRateDto::fromArray(array_merge($data, ['Currency' => $currency]));
    }

    /**
     * Get the commercial cross-rate between two currencies.
     *
     * Returns a plain decimal rate (the API does not return JSON for this endpoint).
     *
     * @param  string  $sellCurrency  ISO 4217 code of the currency being sold.
     * @param  string  $buyCurrency  ISO 4217 code of the currency being bought.
     */
    public function crossRate(string $sellCurrency, string $buyCurrency): float
    {
        $body = $this->http->getRaw(
            'bonline',
            $this->baseUrl.'/rates/commercial/'.urlencode($sellCurrency).'/'.urlencode($buyCurrency),
        );

        return (float) trim($body, " \t\n\r\0\x0B\"");
    }

    /**
     * Get the National Bank of Georgia (NBG) official rate for a currency.
     *
     * Returns a plain decimal rate (the API does not return JSON for this endpoint).
     *
     * @param  string  $currency  ISO 4217 currency code (e.g. USD, EUR).
     */
    public function nbg(string $currency): float
    {
        $body = $this->http->getRaw(
            'bonline',
            $this->baseUrl.'/rates/nbg/'.urlencode($currency),
        );

        return (float) trim($body, " \t\n\r\0\x0B\"");
    }

    /**
     * Get NBG rate history for a currency over a date range.
     *
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string  $startDate  Start date (Y-m-d format).
     * @param  string  $endDate  End date (Y-m-d format).
     * @return array<string, mixed>
     */
    public function nbgHistory(string $currency, string $startDate, string $endDate): array
    {
        return $this->http->get(
            'bonline',
            $this->baseUrl.'/rates/nbg/'.urlencode($currency).'/'.urlencode($startDate).'/'.urlencode($endDate),
        );
    }
}
