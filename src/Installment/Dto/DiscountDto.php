<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Installment\Dto;

/**
 * Installment discount plan DTO returned by the calculator API.
 */
final readonly class DiscountDto
{
    public function __construct(
        public string $code,
        public string $description,
        public float $amount,
        public int $month,
    ) {}

    /**
     * Create an instance from the API response array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: (string) ($data['code'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            amount: (float) ($data['amount'] ?? 0),
            month: (int) ($data['month'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'description' => $this->description,
            'amount' => $this->amount,
            'month' => $this->month,
        ];
    }
}
