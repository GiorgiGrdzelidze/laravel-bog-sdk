<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Installment\Dto;

/**
 * A single item in an installment order basket.
 */
final readonly class InstallmentBasketItemDto
{
    public function __construct(
        public string $productId,
        public float $totalItemAmount,
        public int $totalItemQty,
    ) {}

    /**
     * Create an instance from an API response array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (string) ($data['product_id'] ?? ''),
            totalItemAmount: (float) ($data['total_item_amount'] ?? 0),
            totalItemQty: (int) ($data['total_item_qty'] ?? 1),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'total_item_amount' => $this->totalItemAmount,
            'total_item_qty' => $this->totalItemQty,
        ];
    }
}
