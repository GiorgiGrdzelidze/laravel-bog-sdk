<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\IPay\Dto;

/**
 * A single item in an iPay order basket.
 */
final readonly class IPayItemDto
{
    public function __construct(
        public string $productId,
        public string $description,
        public int $quantity,
        public float $unitPrice,
    ) {}

    /** @param  array<string, mixed>  $data */
    public static function fromArray(array $data): self
    {
        return new self(
            productId: (string) ($data['product_id'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            quantity: (int) ($data['quantity'] ?? 1),
            unitPrice: (float) ($data['unit_price'] ?? 0),
        );
    }

    /** @return array<string, mixed> */
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
