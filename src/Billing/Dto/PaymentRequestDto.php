<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Billing\Dto;

/**
 * Request DTO for creating a billing payment.
 */
final readonly class PaymentRequestDto
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public float $amount,
        public string $currency,
        public string $description,
        public ?string $externalId = null,
        public ?array $metadata = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'external_id' => $this->externalId,
            'metadata' => $this->metadata,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
