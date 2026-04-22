<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Installment\Dto;

/**
 * Order details DTO returned when querying an installment order.
 */
final readonly class InstallmentOrderDetailsDto
{
    /**
     * @param  array<string, mixed>  $rawData  The full raw API response.
     */
    public function __construct(
        public string $orderId,
        public string $status,
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
