<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Dto;

/**
 * Currency exchange rate DTO for commercial and NBG rates.
 */
final readonly class CurrencyRateDto
{
    public function __construct(
        public string $fromCurrency,
        public string $toCurrency,
        public float $buyRate,
        public float $sellRate,
        public ?float $nbgRate,
        public ?string $date,
        /** @var array<string, mixed> */
        public array $rawData,
    ) {}

    /**
     * Create a CurrencyRateDto from the Bonline API response.
     *
     * Handles both PascalCase and camelCase field names.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fromCurrency: (string) ($data['FromCurrency'] ?? $data['fromCurrency'] ?? $data['Currency'] ?? ''),
            toCurrency: (string) ($data['ToCurrency'] ?? $data['toCurrency'] ?? 'GEL'),
            buyRate: (float) ($data['BuyRate'] ?? $data['buyRate'] ?? $data['Buy'] ?? 0),
            sellRate: (float) ($data['SellRate'] ?? $data['sellRate'] ?? $data['Sell'] ?? 0),
            nbgRate: ($data['NbgRate'] ?? $data['nbgRate'] ?? null) !== null ? (float) ($data['NbgRate'] ?? $data['nbgRate']) : null,
            date: $data['Date'] ?? $data['date'] ?? null,
            rawData: $data,
        );
    }

    /**
     * Convert the DTO back to the raw data array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->rawData;
    }
}
