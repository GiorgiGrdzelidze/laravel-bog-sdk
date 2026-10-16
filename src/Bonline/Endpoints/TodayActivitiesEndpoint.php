<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Endpoints;

use GiorgiGrdzelidze\BogSdk\Bonline\Dto\TodayActivityDto;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Bonline endpoint for today's intraday account activities.
 */
final class TodayActivitiesEndpoint
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Get today's transaction activities for an account.
     *
     * @param  string  $accountNumber  IBAN account number.
     * @param  string  $currency  ISO 4217 currency code.
     * @return TodayActivityDto[]
     */
    public function get(string $accountNumber, string $currency): array
    {
        $url = $this->baseUrl.'/documents/v2/todayactivities/'
            .urlencode($accountNumber).'/'
            .urlencode($currency);

        $data = $this->http->get('bonline', $url);

        $records = array_is_list($data) ? $data : (array) ($data['Records'] ?? []);

        return array_map(
            static fn (array $record): TodayActivityDto => TodayActivityDto::fromArray($record),
            array_values(array_filter($records, 'is_array')),
        );
    }
}
