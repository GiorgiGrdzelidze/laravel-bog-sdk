<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\IPay\Dto;

/**
 * Request DTO for creating an iPay checkout order.
 */
final readonly class IPayOrderRequestDto
{
    /**
     * @param  IPayItemDto[]  $items
     */
    public function __construct(
        public string $intent,
        public float $amount,
        public string $currency,
        public array $items,
        public string $callbackUrl,
        public ?string $redirectUrl = null,
        public ?string $externalOrderId = null,
        public bool $saveCard = false,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'intent' => $this->intent,
            'purchase_units' => [
                'amount' => [
                    'currency_code' => $this->currency,
                    'value' => $this->amount,
                ],
                'items' => array_map(
                    static fn (IPayItemDto $item): array => $item->toArray(),
                    $this->items,
                ),
            ],
            'callback_url' => $this->callbackUrl,
        ];

        if ($this->redirectUrl !== null) {
            $data['redirect_url'] = $this->redirectUrl;
        }
        if ($this->externalOrderId !== null) {
            $data['external_order_id'] = $this->externalOrderId;
        }
        if ($this->saveCard) {
            $data['save_card'] = true;
        }

        return $data;
    }
}
