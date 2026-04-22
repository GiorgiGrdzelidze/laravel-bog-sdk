<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\OpenBanking;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\OpenBanking\Identity\IdentityClient;

/**
 * BOG Open Banking API client.
 */
final class OpenBankingClient
{
    private ?IdentityClient $identityClient = null;

    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Get the identity verification client.
     */
    public function identity(): IdentityClient
    {
        return $this->identityClient ??= new IdentityClient($this->http, $this->baseUrl);
    }
}
