<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Dto;

/**
 * Account and amount pair for split payment distribution.
 */
final readonly class SplitAccountDto
{
    public function __construct(
        public string $accountNumber,
        public float $amount,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accountNumber: (string) ($data['account_number'] ?? ''),
            amount: (float) ($data['amount'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'account_number' => $this->accountNumber,
            'amount' => $this->amount,
        ];
    }
}
