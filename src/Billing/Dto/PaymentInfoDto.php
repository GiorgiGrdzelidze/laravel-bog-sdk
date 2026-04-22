<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Billing\Dto;

/**
 * DTO for sending additional information about a billing payment.
 */
final readonly class PaymentInfoDto
{
    /**
     * @param  array<string, mixed>  $info
     */
    public function __construct(
        public string $paymentId,
        public array $info,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'info' => $this->info,
        ];
    }
}
