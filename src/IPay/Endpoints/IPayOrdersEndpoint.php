<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\IPay\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayOrderRequestDto;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayOrderResponseDto;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayPaymentDetailsDto;

/**
 * iPay endpoint for checkout order operations.
 */
final class IPayOrdersEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Create a new iPay checkout order.
     */
    public function create(IPayOrderRequestDto $request): IPayOrderResponseDto
    {
        $data = $this->http->post('ipay', $this->baseUrl.'/checkout/orders', $request->toArray());

        return IPayOrderResponseDto::fromArray($data);
    }

    /**
     * Get payment details for an iPay order.
     */
    public function get(string $orderId): IPayPaymentDetailsDto
    {
        $data = $this->http->get('ipay', $this->baseUrl.'/checkout/payment/'.$orderId);

        return IPayPaymentDetailsDto::fromArray($data);
    }

    /**
     * Refund an iPay order (full or partial).
     *
     * @param  float|null  $amount  Partial refund amount, or null for full refund.
     * @return array<string, mixed>
     */
    public function refund(string $orderId, ?float $amount = null): array
    {
        $body = $amount !== null ? ['amount' => $amount] : [];

        return $this->http->post('ipay', $this->baseUrl.'/checkout/refund/'.$orderId, $body);
    }

    /**
     * Create a subscription charge against an iPay order.
     *
     * @return array<string, mixed>
     */
    public function subscription(string $orderId, float $amount): array
    {
        return $this->http->post('ipay', $this->baseUrl.'/checkout/payment/subscription/'.$orderId, [
            'amount' => $amount,
        ]);
    }

    /**
     * Confirm a pre-authorized iPay payment.
     *
     * @param  float|null  $amount  Amount to capture, or null for full amount.
     * @return array<string, mixed>
     */
    public function preAuthConfirm(string $orderId, ?float $amount = null): array
    {
        $body = $amount !== null ? ['amount' => $amount] : [];

        return $this->http->post('ipay', $this->baseUrl.'/checkout/payment/pre/auth/'.$orderId, $body);
    }
}
