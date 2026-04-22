<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\IPay;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\IPay\Endpoints\IPayOrdersEndpoint;

/**
 * BOG iPay (legacy) API client for checkout orders and subscriptions.
 */
final class IPayClient
{
    private ?IPayOrdersEndpoint $ordersEndpoint = null;

    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Get the iPay orders endpoint for creating, querying, and refunding orders.
     */
    public function orders(): IPayOrdersEndpoint
    {
        return $this->ordersEndpoint ??= new IPayOrdersEndpoint($this->http, $this->baseUrl);
    }
}
