<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Billing\Dto;

/**
 * Response DTO from cancelling a billing payment.
 */
final readonly class CancelResponseDto
{
    public function __construct(
        public string $paymentId,
        public string $status,
        public array $rawData,
    ) {}

    /** @param  array<string, mixed>  $data */
    public static function fromArray(array $data): self
    {
        return new self(
            paymentId: (string) ($data['payment_id'] ?? $data['id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            rawData: $data,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->rawData;
    }
}
