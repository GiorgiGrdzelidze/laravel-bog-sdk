<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\SplitAccountDto;

/**
 * Payments endpoint for split payment distribution across multiple accounts.
 */
final class SplitPaymentEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Create a split payment to distribute funds across multiple accounts.
     *
     * @param  string  $orderId  The BOG order UUID.
     * @param  SplitAccountDto[]  $accounts  List of account/amount pairs.
     * @return array<string, mixed>
     */
    public function create(string $orderId, array $accounts): array
    {
        return $this->http->post('payments', $this->baseUrl.'/services/split-payment/create', [
            'order_id' => $orderId,
            'accounts' => array_map(
                static fn (SplitAccountDto $a): array => $a->toArray(),
                $accounts,
            ),
        ]);
    }
}
