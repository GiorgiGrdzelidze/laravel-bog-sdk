<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Dto;

/**
 * A single page of statement records, with pagination metadata.
 */
final readonly class StatementPageDto
{
    /**
     * @param  TransactionDto[]  $records
     */
    public function __construct(
        public string $id,
        public int $count,
        public int $totalCount,
        public array $records,
    ) {}

    /**
     * Create a StatementPageDto from the Bonline API response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $records = array_map(
            static fn (array $record): TransactionDto => TransactionDto::fromArray($record),
            (array) ($data['Records'] ?? []),
        );

        return new self(
            id: (string) ($data['Id'] ?? ''),
            count: (int) ($data['Count'] ?? 0),
            totalCount: (int) ($data['TotalCount'] ?? 0),
            records: $records,
        );
    }

    /**
     * Convert the page DTO back to the Bonline API format.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'Id' => $this->id,
            'Count' => $this->count,
            'TotalCount' => $this->totalCount,
            'Records' => array_map(
                static fn (TransactionDto $r): array => $r->toArray(),
                $this->records,
            ),
        ];
    }
}
