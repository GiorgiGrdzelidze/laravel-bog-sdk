<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Installment\Dto;

/**
 * Request DTO for the installment calculator API.
 */
final readonly class CalculatorRequestDto
{
    /**
     * @param  InstallmentBasketItemDto[]  $basket
     */
    public function __construct(
        public string $clientType,
        public string $invoiceCurrency,
        public array $basket,
        public float $totalItemAmount,
        public float $totalAmount,
    ) {}

    /**
     * Convert to the API request payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'client' => ['type' => $this->clientType],
            'invoice_currency' => $this->invoiceCurrency,
            'basket' => array_map(
                static fn (InstallmentBasketItemDto $item): array => $item->toArray(),
                $this->basket,
            ),
            'total_item_amount' => $this->totalItemAmount,
            'total_amount' => $this->totalAmount,
        ];
    }
}
