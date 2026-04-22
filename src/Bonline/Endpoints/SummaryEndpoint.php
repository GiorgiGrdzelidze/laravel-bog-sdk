<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Bonline endpoint for statement balance summaries.
 */
final class SummaryEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Get global and daily balances for a statement (v1 - requires statementId).
     *
     * @param  string  $accountNumber  IBAN account number.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  int  $statementId  The ID returned from the statement endpoint.
     * @return array<string, mixed>
     */
    public function get(string $accountNumber, string $currency, int $statementId): array
    {
        $url = $this->baseUrl.'/statement/summary/'
            .urlencode($accountNumber).'/'
            .urlencode($currency).'/'
            .$statementId;

        return $this->http->get('bonline', $url);
    }

    /**
     * Get statement summary for a date range (v2 - no statementId needed).
     *
     * @param  string  $accountNumber  IBAN account number.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string  $startDate  Start date (Y-m-d format).
     * @param  string  $endDate  End date (Y-m-d format).
     * @return array<string, mixed>
     */
    public function forPeriod(string $accountNumber, string $currency, string $startDate, string $endDate): array
    {
        $url = $this->baseUrl.'/statement/v2/summary/'
            .urlencode($accountNumber).'/'
            .urlencode($currency).'/'
            .urlencode($startDate).'/'
            .urlencode($endDate);

        return $this->http->get('bonline', $url);
    }
}
