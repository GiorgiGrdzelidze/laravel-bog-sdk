<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Dto;

/**
 * Statement summary (opening/closing balance and turnover) DTO.
 */
final readonly class SummaryDto
{
    public function __construct(
        public float $openingBalance,
        public float $closingBalance,
        public float $debitTurnover,
        public float $creditTurnover,
        public string $currency,
    ) {}

    /**
     * Create a SummaryDto from the Bonline API response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            openingBalance: (float) ($data['OpeningBalance'] ?? 0),
            closingBalance: (float) ($data['ClosingBalance'] ?? 0),
            debitTurnover: (float) ($data['DebitTurnover'] ?? 0),
            creditTurnover: (float) ($data['CreditTurnover'] ?? 0),
            currency: (string) ($data['Currency'] ?? ''),
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
            'OpeningBalance' => $this->openingBalance,
            'ClosingBalance' => $this->closingBalance,
            'DebitTurnover' => $this->debitTurnover,
            'CreditTurnover' => $this->creditTurnover,
            'Currency' => $this->currency,
        ];
    }
}
