<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Billing\Dto;

/**
 * Response DTO from creating a billing payment.
 */
final readonly class PaymentResponseDto
{
    public function __construct(
        public string $paymentId,
        public string $status,
        public ?string $redirectUrl,
        public array $rawData,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            paymentId: (string) ($data['payment_id'] ?? $data['id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            redirectUrl: $data['redirect_url'] ?? null,
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
