<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Installment\Endpoints;

use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\Installment\Dto\CalculatorRequestDto;
use GiorgiGrdzelidze\BogSdk\Installment\Dto\DiscountDto;

/**
 * Installment calculator endpoint for computing available discount plans.
 */
final class CalculatorEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Calculate available installment discount plans for the given basket.
     *
     * @return DiscountDto[]
     */
    public function discounts(CalculatorRequestDto $request): array
    {
        $data = $this->http->post(
            'installment',
            $this->baseUrl.'/services/installment/calculator',
            $request->toArray(),
        );

        return array_map(
            static fn (array $d): DiscountDto => DiscountDto::fromArray($d),
            (array) ($data['discounts'] ?? []),
        );
    }
}
