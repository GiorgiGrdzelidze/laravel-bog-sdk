<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Payments endpoint for saved card charges and subscription billing.
 */
final class CardChargesEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Charge a saved card linked to a parent order.
     *
     * @param  string  $parentOrderId  The original order ID that saved the card.
     * @param  float  $amount  Amount to charge.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string|null  $externalOrderId  Optional merchant order reference.
     * @return array<string, mixed>
     */
    public function charge(string $parentOrderId, float $amount, string $currency = 'GEL', ?string $externalOrderId = null): array
    {
        $body = [
            'amount' => $amount,
            'currency' => $currency,
        ];

        if ($externalOrderId !== null) {
            $body['external_order_id'] = $externalOrderId;
        }

        return $this->http->post('payments', $this->baseUrl.'/charges/card/'.$parentOrderId, $body);
    }

    /**
     * Create a subscription charge against a saved card.
     *
     * @param  string  $parentOrderId  The original order ID that saved the card.
     * @param  float  $amount  Subscription charge amount.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string|null  $externalOrderId  Optional merchant order reference.
     * @return array<string, mixed>
     */
    public function subscription(string $parentOrderId, float $amount, string $currency = 'GEL', ?string $externalOrderId = null): array
    {
        $body = [
            'amount' => $amount,
            'currency' => $currency,
        ];

        if ($externalOrderId !== null) {
            $body['external_order_id'] = $externalOrderId;
        }

        return $this->http->post('payments', $this->baseUrl.'/charges/card/subscription/'.$parentOrderId, $body);
    }

    /**
     * Delete a saved card linked to a parent order.
     *
     * @return array<string, mixed>
     */
    public function deleteCard(string $parentOrderId): array
    {
        return $this->http->delete('payments', $this->baseUrl.'/charges/card/'.$parentOrderId);
    }
}
