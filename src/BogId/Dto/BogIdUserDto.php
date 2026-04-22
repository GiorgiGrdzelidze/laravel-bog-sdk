<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\BogId\Dto;

/**
 * User profile DTO returned from the BOG-ID userinfo endpoint.
 */
final readonly class BogIdUserDto
{
    /**
     * @param  array<string, mixed>  $rawData  The full raw API response.
     */
    public function __construct(
        public string $sub,
        public ?string $name,
        public ?string $givenName,
        public ?string $familyName,
        public ?string $email,
        public ?bool $emailVerified,
        public ?string $phoneNumber,
        public ?string $personalNumber,
        public array $rawData,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sub: (string) ($data['sub'] ?? ''),
            name: $data['name'] ?? null,
            givenName: $data['given_name'] ?? null,
            familyName: $data['family_name'] ?? null,
            email: $data['email'] ?? null,
            emailVerified: isset($data['email_verified']) ? (bool) $data['email_verified'] : null,
            phoneNumber: $data['phone_number'] ?? null,
            personalNumber: $data['personal_number'] ?? null,
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
