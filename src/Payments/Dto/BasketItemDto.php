<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Dto;

/**
 * A single item in a payment order basket.
 */
final readonly class BasketItemDto
{
    public function __construct(
        public string $productId,
        public string $description,
        public int $quantity,
        public float $unitPrice,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (string) ($data['product_id'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            quantity: (int) ($data['quantity'] ?? 1),
            unitPrice: (float) ($data['unit_price'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
        ];
    }
}
