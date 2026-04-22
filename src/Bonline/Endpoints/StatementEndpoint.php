<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Endpoints;

use DateTimeInterface;
use Generator;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\StatementPageDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\TransactionDto;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Bonline endpoint for account statements (v2 API).
 *
 * Max 1000 transactions per request. Use page() for additional records.
 * Pages must be fetched sequentially — skipping is not allowed.
 */
final class StatementEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Generate a statement for a date range (first page, up to 1000 records).
     *
     * @param  DateTimeInterface  $from  Period start date.
     * @param  DateTimeInterface  $to  Period end date.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string  $accountNumber  IBAN account number.
     */
    public function forPeriod(
        DateTimeInterface $from,
        DateTimeInterface $to,
        string $currency,
        string $accountNumber,
    ): StatementPageDto {
        $url = $this->baseUrl.'/statement/v2/'
            .urlencode($accountNumber).'/'
            .urlencode($currency).'/'
            .$from->format('Y-m-d').'/'
            .$to->format('Y-m-d');

        $data = $this->http->get('bonline', $url);

        return StatementPageDto::fromArray($data);
    }

    /**
     * Fetch a specific page from an already-generated statement.
     *
     * Pages must be fetched sequentially (no skipping).
     * Returns a flat array of TransactionDto (paging endpoint returns an array, not wrapped object).
     *
     * @param  string  $accountNumber  IBAN account number.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  int  $statementId  The ID returned from the initial forPeriod() call.
     * @param  int  $pageNumber  Page number to fetch (sequential, starting from 2).
     * @return TransactionDto[]
     */
    public function page(
        string $accountNumber,
        string $currency,
        int $statementId,
        int $pageNumber,
    ): array {
        $url = $this->baseUrl.'/statement/v2/'
            .urlencode($accountNumber).'/'
            .urlencode($currency).'/'
            .$statementId.'/'
            .$pageNumber;

        $data = $this->http->get('bonline', $url);

        return array_map(
            static fn (array $record): TransactionDto => TransactionDto::fromArray($record),
            (array) $data,
        );
    }

    /**
     * Auto-paginating generator that yields individual TransactionDto objects.
     *
     * Fetches the first page, then automatically loads subsequent pages
     * sequentially until all records have been yielded.
     *
     * @param  DateTimeInterface  $from  Period start date.
     * @param  DateTimeInterface  $to  Period end date.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string  $accountNumber  IBAN account number.
     * @return Generator<int, TransactionDto>
     */
    public function stream(
        DateTimeInterface $from,
        DateTimeInterface $to,
        string $currency,
        string $accountNumber,
    ): Generator {
        $firstPage = $this->forPeriod($from, $to, $currency, $accountNumber);
        $yielded = 0;

        foreach ($firstPage->records as $record) {
            yield $record;
            $yielded++;
        }

        if ($firstPage->id === '' || $yielded >= $firstPage->count) {
            return;
        }

        $pageNumber = 2;
        while ($yielded < $firstPage->count) {
            $records = $this->page($accountNumber, $currency, (int) $firstPage->id, $pageNumber);

            foreach ($records as $record) {
                yield $record;
                $yielded++;
            }

            if (count($records) === 0) {
                break;
            }

            $pageNumber++;
        }
    }
}
