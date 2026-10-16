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
        public ?int $accountKey = null,
        public ?float $statementBalance = null,
    ) {}

    /**
     * Create a BalanceDto from the Bonline API response.
     *
     * Bonline answers with AccountKey, AvailableBalance, CurrentBalance and StatementBalance only,
     * so the account and currency come from the request and the blocked amount is derived.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, string $accountNumber = '', string $currency = ''): self
    {
        $available = (float) ($data['AvailableBalance'] ?? 0);
        $current = (float) ($data['CurrentBalance'] ?? 0);

        return new self(
            accountNumber: (string) ($data['AccountNumber'] ?? $accountNumber),
            currency: (string) ($data['Currency'] ?? $currency),
            availableBalance: $available,
            currentBalance: $current,
            blockedAmount: isset($data['BlockedAmount']) ? (float) $data['BlockedAmount'] : round(max(0.0, $current - $available), 2),
            accountKey: isset($data['AccountKey']) ? (int) $data['AccountKey'] : null,
            statementBalance: isset($data['StatementBalance']) ? (float) $data['StatementBalance'] : null,
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
            'AccountKey' => $this->accountKey,
            'AvailableBalance' => $this->availableBalance,
            'CurrentBalance' => $this->currentBalance,
            'BlockedAmount' => $this->blockedAmount,
            'StatementBalance' => $this->statementBalance,
        ];
    }
}
