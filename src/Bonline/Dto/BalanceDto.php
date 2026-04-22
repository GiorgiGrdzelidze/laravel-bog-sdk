<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Dto;

/**
 * Account balance information DTO.
 */
final readonly class BalanceDto
{
    public function __construct(
        public string $accountNumber,
        public string $currency,
        public float $availableBalance,
        public float $currentBalance,
        public float $blockedAmount,
    ) {}

    /**
     * Create a BalanceDto from the Bonline API response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accountNumber: (string) ($data['AccountNumber'] ?? ''),
            currency: (string) ($data['Currency'] ?? ''),
            availableBalance: (float) ($data['AvailableBalance'] ?? 0),
            currentBalance: (float) ($data['CurrentBalance'] ?? 0),
            blockedAmount: (float) ($data['BlockedAmount'] ?? 0),
        );
    }

    /**
     * Convert the DTO to an array matching the Bonline API format.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'AccountNumber' => $this->accountNumber,
            'Currency' => $this->currency,
            'AvailableBalance' => $this->availableBalance,
            'CurrentBalance' => $this->currentBalance,
            'BlockedAmount' => $this->blockedAmount,
        ];
    }
}
