<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Bonline accounts endpoint for INN verification.
 */
final class AccountsEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Check an INN (identification/tax number) against BOG records.
     *
     * @return array<string, mixed>
     */
    public function checkInn(string $inn): array
    {
        return $this->http->post('bonline', $this->baseUrl.'/accounts/checkInn', [
            'Inn' => $inn,
        ]);
    }
}
