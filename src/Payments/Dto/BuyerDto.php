<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Dto;

/**
 * Buyer information for a payment order (optional).
 */
final readonly class BuyerDto
{
    public function __construct(
        public ?string $fullName = null,
        public ?string $email = null,
        public ?string $phoneNumber = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fullName: isset($data['full_name']) ? (string) $data['full_name'] : null,
            email: isset($data['masked_email']) ? (string) $data['masked_email'] : null,
            phoneNumber: isset($data['masked_phone']) ? (string) $data['masked_phone'] : null,
        );
    }

    /**
     * Convert to the BOG buyer object. BOG names the contact fields
     * masked_email / masked_phone (it masks them for the payment page).
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'full_name' => $this->fullName,
            'masked_email' => $this->email,
            'masked_phone' => $this->phoneNumber,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
