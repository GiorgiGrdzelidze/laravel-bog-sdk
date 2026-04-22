<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Dto;

/**
 * Request DTO for creating a new e-commerce payment order.
 */
final readonly class CreateOrderRequestDto
{
    /**
     * @param  BasketItemDto[]  $basket
     * @param  string[]  $paymentMethods  Allowed payment methods (default: ['card']).
     * @param  int  $ttl  Order time-to-live in minutes.
     * @param  string  $capture  Capture method: 'automatic' or 'manual' (pre-auth).
     * @param  bool  $saveCard  Whether to save the card for future charges.
     * @param  string|null  $saveCardToDate  Card save expiry date (MM/YY format).
     * @param  array<string, mixed>|null  $config  Additional configuration options.
     */
    public function __construct(
        public string $callbackUrl,
        public string $externalOrderId,
        public string $currency,
        public float $totalAmount,
        public array $basket,
        public ?string $successUrl = null,
        public ?string $failUrl = null,
        public array $paymentMethods = ['card'],
        public int $ttl = 15,
        public ?BuyerDto $buyer = null,
        public string $capture = 'automatic',
        public bool $saveCard = false,
        public ?string $saveCardToDate = null,
        public ?array $config = null,
    ) {}

    /**
     * Convert the DTO to the BOG Payments API request format.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'callback_url' => $this->callbackUrl,
            'external_order_id' => $this->externalOrderId,
            'purchase_units' => [
                'currency' => $this->currency,
                'total_amount' => $this->totalAmount,
                'basket' => array_map(
                    static fn (BasketItemDto $item): array => $item->toArray(),
                    $this->basket,
                ),
            ],
            'payment_method' => $this->paymentMethods,
            'ttl' => $this->ttl,
            'capture' => $this->capture,
        ];

        if ($this->successUrl !== null || $this->failUrl !== null) {
            $data['redirect_urls'] = array_filter([
                'success' => $this->successUrl,
                'fail' => $this->failUrl,
            ], static fn (mixed $v): bool => $v !== null);
        }

        if ($this->buyer !== null) {
            $data['buyer'] = $this->buyer->toArray();
        }

        if ($this->saveCard) {
            $data['save_card'] = true;
            if ($this->saveCardToDate !== null) {
                $data['save_card_to_date'] = $this->saveCardToDate;
            }
        }

        if ($this->config !== null) {
            $data['config'] = $this->config;
        }

        return $data;
    }
}
