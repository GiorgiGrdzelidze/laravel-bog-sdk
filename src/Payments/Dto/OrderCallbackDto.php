<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Dto;

/**
 * Parsed DTO from a verified BOG payment callback.
 *
 * BOG wraps the order payload under a top-level `body` key:
 * `{ "event": "order_payment", "zoned_request_time": "...", "body": { ...order... } }`.
 */
final readonly class OrderCallbackDto
{
    public function __construct(
        public string $id,
        public string $statusKey,
        public ?string $externalOrderId,
        public ?float $totalAmount,
        public ?string $currency,
        /** @var array<string, mixed> */
        public array $rawData,
        public ?string $event = null,
        public ?string $zonedRequestTime = null,
        public ?float $requestAmount = null,
        public ?float $transferAmount = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $body = isset($data['body']) && is_array($data['body']) ? $data['body'] : $data;
        $purchaseUnits = isset($body['purchase_units']) && is_array($body['purchase_units']) ? $body['purchase_units'] : [];

        $requestAmount = isset($purchaseUnits['request_amount']) ? (float) $purchaseUnits['request_amount'] : null;
        $transferAmount = isset($purchaseUnits['transfer_amount']) ? (float) $purchaseUnits['transfer_amount'] : null;
        $totalAmount = $transferAmount ?? $requestAmount
            ?? (isset($purchaseUnits['total_amount']) ? (float) $purchaseUnits['total_amount'] : null);

        return new self(
            id: (string) ($body['order_id'] ?? $body['id'] ?? ''),
            statusKey: (string) (is_array($body['order_status'] ?? null) ? ($body['order_status']['key'] ?? '') : ''),
            externalOrderId: $body['external_order_id'] ?? null,
            totalAmount: $totalAmount,
            currency: $purchaseUnits['currency_code'] ?? $purchaseUnits['currency'] ?? null,
            rawData: $data,
            event: isset($data['event']) ? (string) $data['event'] : null,
            zonedRequestTime: isset($data['zoned_request_time']) ? (string) $data['zoned_request_time'] : null,
            requestAmount: $requestAmount,
            transferAmount: $transferAmount,
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
