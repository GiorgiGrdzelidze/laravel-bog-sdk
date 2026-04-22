<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Installment\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\Installment\Dto\InstallmentOrderDetailsDto;

/**
 * Installment checkout endpoint for creating and querying installment orders.
 */
final class CheckoutEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Create a new installment checkout order.
     *
     * @param  array<string, mixed>  $orderData
     * @return array<string, mixed>
     */
    public function create(array $orderData): array
    {
        return $this->http->post(
            'installment',
            $this->baseUrl.'/services/installment/checkout',
            $orderData,
        );
    }

    /**
     * Get the details of an existing installment order.
     */
    public function details(string $orderId): InstallmentOrderDetailsDto
    {
        $data = $this->http->get(
            'installment',
            $this->baseUrl.'/services/installment/details/'.$orderId,
        );

        return InstallmentOrderDetailsDto::fromArray($data);
    }
}
