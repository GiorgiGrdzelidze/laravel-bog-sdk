<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\CreateOrderRequestDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\CreateOrderResponseDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\OrderDetailsDto;

/**
 * Payments endpoint for e-commerce order operations.
 */
final class OrdersEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Create a new e-commerce payment order.
     */
    public function create(CreateOrderRequestDto $request): CreateOrderResponseDto
    {
        $data = $this->http->post('payments', $this->baseUrl.'/ecommerce/orders', $request->toArray());

        return CreateOrderResponseDto::fromArray($data);
    }

    /**
     * Get order details and payment status by order ID.
     */
    public function get(string $orderId): OrderDetailsDto
    {
        $data = $this->http->get('payments', $this->baseUrl.'/receipt/'.$orderId);

        return OrderDetailsDto::fromArray($data);
    }

    /**
     * Refund an order (full or partial).
     *
     * @param  string  $orderId  The BOG order UUID.
     * @param  float|null  $amount  Partial refund amount, or null for full refund.
     * @return array<string, mixed>
     */
    public function refund(string $orderId, ?float $amount = null): array
    {
        $body = $amount !== null ? ['amount' => $amount] : [];

        return $this->http->post('payments', $this->baseUrl.'/services/orders/refund/'.$orderId, $body);
    }

    /**
     * Cancel an order before completion.
     *
     * @return array<string, mixed>
     */
    public function cancel(string $orderId): array
    {
        return $this->http->post('payments', $this->baseUrl.'/services/orders/'.$orderId.'/cancel');
    }

    /**
     * Confirm a pre-authorized (capture=manual) order.
     *
     * @param  string  $orderId  The BOG order UUID.
     * @param  float|null  $amount  Amount to capture, or null for full amount.
     * @return array<string, mixed>
     */
    public function confirm(string $orderId, ?float $amount = null): array
    {
        $body = $amount !== null ? ['amount' => $amount] : [];

        return $this->http->post('payments', $this->baseUrl.'/services/orders/'.$orderId.'/confirm', $body);
    }
}
