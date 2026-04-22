<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Endpoints;

use GiorgiGrdzelidze\BogSdk\Bonline\Dto\BalanceDto;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Bonline endpoint for querying account balances.
 */
final class BalanceEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Get available and current balance for an account.
     *
     * @param  string  $accountNumber  IBAN account number.
     * @param  string  $currency  ISO 4217 currency code (e.g. GEL, USD).
     * @param  bool  $initStatementBalance  Whether to initialize statement balance data.
     */
    public function get(string $accountNumber, string $currency, bool $initStatementBalance = false): BalanceDto
    {
        $url = $this->baseUrl.'/accounts/'
            .urlencode($accountNumber).'/'
            .urlencode($currency).'/'
            .($initStatementBalance ? '1' : '0');

        $data = $this->http->get('bonline', $url);

        return BalanceDto::fromArray($data);
    }
}
