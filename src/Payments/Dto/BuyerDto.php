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
            email: isset($data['email']) ? (string) $data['email'] : null,
            phoneNumber: isset($data['phone_number']) ? (string) $data['phone_number'] : null,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'full_name' => $this->fullName,
            'email' => $this->email,
            'phone_number' => $this->phoneNumber,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
