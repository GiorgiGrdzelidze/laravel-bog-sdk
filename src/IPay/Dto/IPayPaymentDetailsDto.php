<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\IPay\Dto;

/**
 * Payment details DTO returned when querying an iPay order's payment status.
 */
final readonly class IPayPaymentDetailsDto
{
    /**
     * @param  array<string, mixed>  $rawData  The full raw API response.
     */
    public function __construct(
        public string $orderId,
        public string $status,
        public ?float $amount,
        public ?string $currency,
        public array $rawData,
    ) {}

    /**
     * Create an instance from the raw API response array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            orderId: (string) ($data['order_id'] ?? $data['id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            currency: $data['currency'] ?? null,
            rawData: $data,
        );
    }

    /**
     * Convert back to the raw API response array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->rawData;
    }
}
