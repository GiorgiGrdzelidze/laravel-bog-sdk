<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Dto;

/**
 * Bonline account information DTO.
 */
final readonly class AccountDto
{
    public function __construct(
        public string $accountNumber,
        public string $currency,
        public string $accountName,
        public string $accountType,
        public ?float $availableBalance,
        public ?float $currentBalance,
        public ?string $iban,
        public ?string $status,
        /** @var array<string, mixed> */
        public array $rawData,
    ) {}

    /**
     * Create an AccountDto from the Bonline API response.
     *
     * Handles both PascalCase and camelCase field names.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accountNumber: (string) ($data['AccountNumber'] ?? $data['accountNumber'] ?? ''),
            currency: (string) ($data['Currency'] ?? $data['currency'] ?? ''),
            accountName: (string) ($data['AccountName'] ?? $data['accountName'] ?? ''),
            accountType: (string) ($data['AccountType'] ?? $data['accountType'] ?? ''),
            availableBalance: isset($data['AvailableBalance']) ? (float) $data['AvailableBalance'] : (isset($data['availableBalance']) ? (float) $data['availableBalance'] : null),
            currentBalance: isset($data['CurrentBalance']) ? (float) $data['CurrentBalance'] : (isset($data['currentBalance']) ? (float) $data['currentBalance'] : null),
            iban: $data['Iban'] ?? $data['IBAN'] ?? $data['iban'] ?? $data['AccountNumber'] ?? null,
            status: $data['Status'] ?? $data['status'] ?? null,
            rawData: $data,
        );
    }

    /**
     * Convert the DTO back to the raw data array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->rawData;
    }
}
