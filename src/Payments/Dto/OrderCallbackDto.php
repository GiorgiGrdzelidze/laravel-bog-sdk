<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Dto;

/**
 * Parsed DTO from a verified BOG payment callback.
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
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            statusKey: (string) ($data['order_status']['key'] ?? ''),
            externalOrderId: $data['external_order_id'] ?? null,
            totalAmount: isset($data['purchase_units']['total_amount']) ? (float) $data['purchase_units']['total_amount'] : null,
            currency: $data['purchase_units']['currency'] ?? null,
            rawData: $data,
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
