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
 * Supports generating statements, paginating through results,
 * and auto-paginating via a generator stream.
 */
final class StatementEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Generate a statement for a date range (first page).
     *
     * @param  DateTimeInterface  $from  Period start date.
     * @param  DateTimeInterface  $to  Period end date.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string  $accountNumber  IBAN account number.
     * @param  bool  $includeToday  Whether to include today's transactions.
     * @param  bool  $orderByDate  Whether to order results by date.
     * @param  int  $take  Maximum records per page.
     */
    public function forPeriod(
        DateTimeInterface $from,
        DateTimeInterface $to,
        string $currency,
        string $accountNumber,
        bool $includeToday = false,
        bool $orderByDate = true,
        int $take = 100,
    ): StatementPageDto {
        $url = $this->baseUrl.'/statement/v2/'
            .urlencode($accountNumber).'/'
            .urlencode($currency).'/'
            .$from->format('Y-m-d').'/'
            .$to->format('Y-m-d').'/'
            .($includeToday ? '1' : '0').'/'
            .($orderByDate ? '1' : '0').'/'
            .$take;

        $data = $this->http->get('bonline', $url);

        return StatementPageDto::fromArray($data);
    }

    /**
     * Fetch a specific page from an already-generated statement.
     *
     * @param  string  $accountNumber  IBAN account number.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  int  $statementId  The ID returned from the initial forPeriod() call.
     * @param  int  $pageNumber  Page number to fetch (1-based).
     * @param  bool  $orderByDate  Whether to order results by date.
     */
    public function page(
        string $accountNumber,
        string $currency,
        int $statementId,
        int $pageNumber,
        bool $orderByDate = true,
    ): StatementPageDto {
        $url = $this->baseUrl.'/statement/v2/'
            .urlencode($accountNumber).'/'
            .urlencode($currency).'/'
            .$statementId.'/'
            .$pageNumber.'/'
            .($orderByDate ? '1' : '0');

        $data = $this->http->get('bonline', $url);

        return StatementPageDto::fromArray($data);
    }

    /**
     * Auto-paginating generator that yields individual TransactionDto objects.
     *
     * Fetches the first page, then automatically loads subsequent pages
     * until all records have been yielded.
     *
     * @param  DateTimeInterface  $from  Period start date.
     * @param  DateTimeInterface  $to  Period end date.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string  $accountNumber  IBAN account number.
     * @param  bool  $includeToday  Whether to include today's transactions.
     * @param  bool  $orderByDate  Whether to order results by date.
     * @param  int  $take  Maximum records per page.
     * @return Generator<int, TransactionDto>
     */
    public function stream(
        DateTimeInterface $from,
        DateTimeInterface $to,
        string $currency,
        string $accountNumber,
        bool $includeToday = false,
        bool $orderByDate = true,
        int $take = 100,
    ): Generator {
        $firstPage = $this->forPeriod($from, $to, $currency, $accountNumber, $includeToday, $orderByDate, $take);
        $yielded = 0;

        foreach ($firstPage->records as $record) {
            yield $record;
            $yielded++;
        }

        if ($firstPage->id === '' || $yielded >= $firstPage->recordCount) {
            return;
        }

        $pageNumber = 2;
        while ($yielded < $firstPage->recordCount) {
            $page = $this->page($accountNumber, $currency, (int) $firstPage->id, $pageNumber, $orderByDate);

            foreach ($page->records as $record) {
                yield $record;
                $yielded++;
            }

            if (count($page->records) === 0) {
                break;
            }

            $pageNumber++;
        }
    }
}
