<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Bonline endpoint for bank requisites (SWIFT/BIC details).
 */
final class RequisitesEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Get bank requisites/SWIFT details for an account.
     *
     * @param  string  $accountNumber  IBAN account number.
     * @param  string  $currency  ISO 4217 currency code.
     * @return array<string, mixed>
     */
    public function get(string $accountNumber, string $currency): array
    {
        return $this->http->get(
            'bonline',
            $this->baseUrl.'/requisites/'.urlencode($accountNumber).'/'.urlencode($currency),
        );
    }
}
