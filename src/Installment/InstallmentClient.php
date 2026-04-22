<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Installment;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\Installment\Endpoints\CalculatorEndpoint;
use GiorgiGrdzelidze\BogSdk\Installment\Endpoints\CheckoutEndpoint;

/**
 * BOG Installment API client for calculator and checkout operations.
 */
final class InstallmentClient
{
    private ?CalculatorEndpoint $calculatorEndpoint = null;

    private ?CheckoutEndpoint $checkoutEndpoint = null;

    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
        private readonly string $shopId,
    ) {}

    /**
     * Get the installment calculator endpoint.
     */
    public function calculator(): CalculatorEndpoint
    {
        return $this->calculatorEndpoint ??= new CalculatorEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the installment checkout endpoint.
     */
    public function checkout(): CheckoutEndpoint
    {
        return $this->checkoutEndpoint ??= new CheckoutEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Build JS SDK configuration array for the installment modal.
     *
     * @param  array<array{product_id: string, total_item_amount: float, total_item_qty: int}>  $basket
     */
    /**
     * @return array<string, mixed>
     */
    public function jsConfig(array $basket, string $onCompleteUrl, string $currency = 'GEL'): array
    {
        $totalAmount = array_sum(array_column($basket, 'total_item_amount'));

        return [
            'shop_id' => $this->shopId,
            'basket' => $basket,
            'currency' => $currency,
            'amount' => $totalAmount,
            'on_complete_url' => $onCompleteUrl,
        ];
    }
}
