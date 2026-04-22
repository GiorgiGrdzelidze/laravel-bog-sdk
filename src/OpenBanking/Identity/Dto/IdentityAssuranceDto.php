<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\OpenBanking\Identity\Dto;

/**
 * Response DTO from an identity assurance verification check.
 */
final readonly class IdentityAssuranceDto
{
    public function __construct(
        public bool $verified,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $confidence,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            verified: (bool) ($data['verified'] ?? false),
            firstName: $data['first_name'] ?? null,
            lastName: $data['last_name'] ?? null,
            confidence: $data['confidence'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'verified' => $this->verified,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'confidence' => $this->confidence,
        ];
    }
}
