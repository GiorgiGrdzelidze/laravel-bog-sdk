<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Payments endpoint for Google Pay payment completion.
 */
final class GooglePayEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Complete a Google Pay payment with the provided token data.
     *
     * @param  array<string, mixed>  $paymentData  Google Pay token and order data.
     * @return array<string, mixed>
     */
    public function complete(array $paymentData): array
    {
        return $this->http->post('payments', $this->baseUrl.'/googlepay/complete', $paymentData);
    }
}
