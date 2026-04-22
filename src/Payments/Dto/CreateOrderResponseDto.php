<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Dto;

/**
 * Response DTO from creating a payment order.
 */
final readonly class CreateOrderResponseDto
{
    public function __construct(
        public string $id,
        public ?string $redirectUrl = null,
        public ?string $detailsUrl = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            redirectUrl: $data['_links']['redirect']['href'] ?? null,
            detailsUrl: $data['_links']['details']['href'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'redirect_url' => $this->redirectUrl,
            'details_url' => $this->detailsUrl,
        ];
    }
}
