<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Dto;

/**
 * Order details DTO with payment status, amount, and card info.
 *
 * Maps the BOG `GET /receipt/{order_id}` response.
 */
final readonly class OrderDetailsDto
{
    public function __construct(
        public string $id,
        public string $statusKey,
        public ?string $externalOrderId,
        public ?float $totalAmount,
        public ?string $currency,
        public ?string $paymentMethod,
        public ?string $cardMask,
        public ?string $rrn,
        /** @var array<string, mixed> */
        public array $rawData,
        public ?float $requestAmount = null,
        public ?float $transferAmount = null,
        public ?float $refundAmount = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $body = isset($data['body']) && is_array($data['body']) ? $data['body'] : $data;
        $purchaseUnits = isset($body['purchase_units']) && is_array($body['purchase_units']) ? $body['purchase_units'] : [];
        $paymentDetail = isset($body['payment_detail']) && is_array($body['payment_detail']) ? $body['payment_detail'] : [];

        $requestAmount = isset($purchaseUnits['request_amount']) ? (float) $purchaseUnits['request_amount'] : null;
        $transferAmount = isset($purchaseUnits['transfer_amount']) ? (float) $purchaseUnits['transfer_amount'] : null;
        $refundAmount = isset($purchaseUnits['refund_amount']) ? (float) $purchaseUnits['refund_amount'] : null;
        $totalAmount = $transferAmount ?? $requestAmount
            ?? (isset($purchaseUnits['total_amount']) ? (float) $purchaseUnits['total_amount'] : null);

        return new self(
            id: (string) ($body['order_id'] ?? $body['id'] ?? ''),
            statusKey: (string) ($body['order_status']['key'] ?? ''),
            externalOrderId: $body['external_order_id'] ?? null,
            totalAmount: $totalAmount,
            currency: $purchaseUnits['currency_code'] ?? $purchaseUnits['currency'] ?? null,
            paymentMethod: $paymentDetail['transfer_method']['key'] ?? $paymentDetail['payment_method'] ?? null,
            cardMask: $paymentDetail['payer_identifier'] ?? $paymentDetail['card_mask'] ?? null,
            rrn: $paymentDetail['request_rrn'] ?? $paymentDetail['rrn'] ?? null,
            rawData: $data,
            requestAmount: $requestAmount,
            transferAmount: $transferAmount,
            refundAmount: $refundAmount,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->rawData;
    }
}
