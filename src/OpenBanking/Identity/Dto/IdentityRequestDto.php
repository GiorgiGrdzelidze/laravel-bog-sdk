<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\OpenBanking\Identity\Dto;

/**
 * Request DTO for identity assurance verification.
 */
final readonly class IdentityRequestDto
{
    public function __construct(
        public string $personalNumber,
        public ?string $documentNumber = null,
        public ?string $birthDate = null,
    ) {}

    /**
     * Convert to the API request payload, excluding null values.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'personal_number' => $this->personalNumber,
            'document_number' => $this->documentNumber,
            'birth_date' => $this->birthDate,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
