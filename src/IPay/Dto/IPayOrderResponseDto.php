<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\IPay\Dto;

/**
 * Response DTO from creating an iPay checkout order.
 */
final readonly class IPayOrderResponseDto
{
    public function __construct(
        public string $orderId,
        public ?string $redirectUrl,
        public string $status,
        public array $rawData,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: (string) ($data['order_id'] ?? $data['id'] ?? ''),
            redirectUrl: $data['redirect_url'] ?? $data['_links']['redirect']['href'] ?? null,
            status: (string) ($data['status'] ?? ''),
            rawData: $data,
        );
    }

    public function toArray(): array
    {
        return $this->rawData;
    }
}
