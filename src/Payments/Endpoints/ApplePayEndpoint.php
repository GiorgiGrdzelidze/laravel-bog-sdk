<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Payments endpoint for Apple Pay payment completion.
 */
final class ApplePayEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Complete an Apple Pay payment with the provided token data.
     *
     * @param  array<string, mixed>  $paymentData  Apple Pay token and order data.
     * @return array<string, mixed>
     */
    public function complete(array $paymentData): array
    {
        return $this->http->post('payments', $this->baseUrl.'/applepay/complete', $paymentData);
    }
}
