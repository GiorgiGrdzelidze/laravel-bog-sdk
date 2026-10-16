<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\BogClient;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\BalanceDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\CurrencyRateDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\StatementPageDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\TodayActivityDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\TransactionDto;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

final class BonlineTest extends TestCase
{
    private function fakeTokenAndEndpoint(string $urlPattern, mixed $response): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'bonline-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            $urlPattern => $response,
        ]);
    }

    private function makeTxFields(array $overrides = []): array
    {
        return array_merge([
            'EntryDate' => '2025-01-15T00:00:00',
            'EntryDocumentNumber' => '0001',
            'EntryAccountNumber' => 'GE00BG0000000000000001GEL',
            'EntryAmountDebit' => 0.0,
            'EntryAmountDebitBase' => 0.0,
            'EntryAmountCredit' => 10.0,
            'EntryAmountCreditBase' => 10.0,
            'EntryAmountBase' => 10.0,
            'EntryAmount' => 10.0,
            'EntryComment' => '',
            'DocumentProductGroup' => 'TRF',
            'DocumentValueDate' => '2025-01-15T00:00:00',
            'SenderDetails' => ['Name' => 'Sender', 'Inn' => '111', 'AccountNumber' => 'GE01', 'BankCode' => 'BAGAGE22', 'BankName' => 'BOG'],
            'BeneficiaryDetails' => ['Name' => 'Beneficiary', 'Inn' => '222', 'AccountNumber' => 'GE02', 'BankCode' => 'BAGAGE22', 'BankName' => 'BOG'],
            'DocumentTreasuryCode' => null,
            'DocumentNomination' => 'Payment',
            'DocumentInformation' => '',
            'DocumentSourceAmount' => 10.0,
            'DocumentSourceCurrency' => 'GEL',
            'DocumentDestinationAmount' => 10.0,
            'DocumentDestinationCurrency' => 'GEL',
            'DocumentReceiveDate' => '2025-01-15T01:00:00',
            'DocumentRate' => null,
            'DocumentKey' => 123456,
            'EntryId' => 999,
            'DocumentPayerName' => 'Payer',
            'DocumentPayerInn' => '111',
            'DocComment' => 'Test comment',
            'AuthDate' => null,
        ], $overrides);
    }

    public function test_statement_for_period(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/2025-01-01/2025-01-31',
            Http::response([
                'Id' => 12345,
                'Count' => 1,
                'TotalCount' => 1,
                'Records' => [
                    $this->makeTxFields([
                        'EntryAmountCredit' => 150.25,
                        'EntryComment' => 'Test payment',
                        'EntryId' => 999,
                    ]),
                ],
            ]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->bonline()->statement()->forPeriod(
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-01-31'),
            'GEL',
            'GE00BG0000000000000001',
        );

        $this->assertInstanceOf(StatementPageDto::class, $result);
        $this->assertSame('12345', $result->id);
        $this->assertSame(1, $result->count);
        $this->assertCount(1, $result->records);
        $this->assertInstanceOf(TransactionDto::class, $result->records[0]);
        $this->assertSame(999, $result->records[0]->entryId);
        $this->assertSame(150.25, $result->records[0]->entryAmountCredit);
        $this->assertSame('Test payment', $result->records[0]->entryComment);
    }

    public function test_statement_paging(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/12345/2',
            Http::response([
                $this->makeTxFields(['EntryId' => 1001]),
            ]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->bonline()->statement()->page('GE00BG0000000000000001', 'GEL', 12345, 2);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(TransactionDto::class, $result[0]);
        $this->assertSame(1001, $result[0]->entryId);
    }

    public function test_statement_stream_auto_paginates(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'bonline-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/2025-01-01/2025-01-31' => Http::response([
                'Id' => 100,
                'Count' => 2,
                'TotalCount' => 2,
                'Records' => [$this->makeTxFields(['EntryId' => 1])],
            ]),
            'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/100/2' => Http::response([
                $this->makeTxFields(['EntryId' => 2]),
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);

        $transactions = [];
        foreach ($client->bonline()->statement()->stream(
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-01-31'),
            'GEL',
            'GE00BG0000000000000001',
        ) as $tx) {
            $transactions[] = $tx;
        }

        $this->assertCount(2, $transactions);
        $this->assertSame(1, $transactions[0]->entryId);
        $this->assertSame(2, $transactions[1]->entryId);
    }

    public function test_today_activities_map_the_feeds_own_fields(): void
    {
        $card = 'გადახდა - თარიღი: 16/10/2026 12:41:31; თანხა:GEL 212.25; საკომისიო: GEL 3.18; ბარათი: VISA, 411634***2444; კოდი: 531133; ტერმინალის ID: POS1NB83; დეპოზ:POS1NB83R9R; LUMINO;  ID:; მისამართი: ქავთარაძის 78';
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/documents/v2/todayactivities/GE00BG0000000000000001/GEL',
            Http::response(['Records' => [
                ['Id' => 127764475501, 'DocKey' => 49608711099, 'DocNo' => '11921057645', 'PostDate' => '2026-10-16T00:00:00', 'ValueDate' => '0001-01-01T00:00:00',
                    'EntryType' => 'TRN', 'EntryComment' => $card, 'EntryCommentEn' => $card, 'Nomination' => $card, 'Credit' => 209.07, 'Debit' => 0, 'Amount' => 209.07, 'AmountBase' => 209.07,
                    'PayerName' => 'შპს მაღაზია', 'PayerInn' => '405000000', 'Sender' => ['Name' => null, 'Inn' => null, 'AccountNumber' => null],
                    'Beneficiary' => ['Name' => 'შპს მაღაზია', 'Inn' => '405000000', 'AccountNumber' => 'GE00BG0000000000000001GEL', 'BankCode' => 'BAGAGE22']],
                ['Id' => 2, 'DocKey' => 3, 'DocNo' => '4', 'PostDate' => '2026-10-16T00:00:00', 'ValueDate' => '2026-10-16T00:00:00', 'EntryType' => 'PMD',
                    'Nomination' => 'თანხის გადარიცხვა', 'Credit' => 81, 'Debit' => 0, 'Amount' => 81, 'AmountBase' => 81],
                ['Id' => 5, 'EntryType' => 'TRN', 'Nomination' => $card, 'Credit' => 0, 'Debit' => 12.5, 'Amount' => -12.5],
            ]]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $activities = $client->bonline()->todayActivities()->get('GE00BG0000000000000001', 'GEL');

        $this->assertCount(3, $activities);
        $this->assertInstanceOf(TodayActivityDto::class, $activities[0]);
        $this->assertSame(127764475501, $activities[0]->id);
        $this->assertSame(209.07, $activities[0]->credit);
        $this->assertSame('2026-10-16T00:00:00', $activities[0]->postDate);
        $this->assertNull($activities[0]->valueDate);
        $this->assertSame('TRN', $activities[0]->entryType);
        $this->assertSame('GE00BG0000000000000001GEL', $activities[0]->beneficiary['AccountNumber']);
        $this->assertTrue($activities[0]->isCredit());
        $payment = $activities[0]->cardPayment();
        $this->assertNotNull($payment);
        $this->assertSame(212.25, $payment->amount);
        $this->assertSame(209.07, $payment->netAmount());
        $this->assertSame('2026-10-16 12:41:31', $payment->purchasedAt->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-16T00:00:00', $activities[1]->valueDate);
        $this->assertNull($activities[1]->cardPayment());
        $this->assertFalse($activities[2]->isCredit());
        $this->assertNull($activities[2]->cardPayment());
        $this->assertSame(127764475501, $activities[0]->toArray()['Id']);
    }

    public function test_today_activities_accept_a_bare_list(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/documents/v2/todayactivities/GE00BG0000000000000001/GEL',
            Http::response([['Id' => 9, 'Credit' => 5, 'Nomination' => 'x']]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);

        $this->assertSame(9, $client->bonline()->todayActivities()->get('GE00BG0000000000000001', 'GEL')[0]->id);
    }

    public function test_statement_stream_pages_until_total_count_not_page_count(): void
    {
        $base = 'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/';
        Http::fake([
            'account-test.bog.ge/*' => Http::response(['access_token' => 'bonline-token', 'expires_in' => 3600, 'token_type' => 'Bearer']),
            $base.'2025-01-01/2025-12-31' => Http::response(['Id' => 4937843466048260, 'Count' => 2, 'TotalCount' => 5, 'Records' => [
                $this->makeTxFields(['EntryId' => 1]), $this->makeTxFields(['EntryId' => 2]),
            ]]),
            $base.'4937843466048260/2' => Http::response(['Id' => 4937843466069302, 'Count' => 2, 'TotalCount' => 5, 'Records' => [
                $this->makeTxFields(['EntryId' => 3]), $this->makeTxFields(['EntryId' => 4]),
            ]]),
            $base.'4937843466048260/3' => Http::response(['Id' => 4937843466069302, 'Count' => 1, 'TotalCount' => 5, 'Records' => [
                $this->makeTxFields(['EntryId' => 5]),
            ]]),
            $base.'*' => Http::response(['Count' => 0, 'TotalCount' => 5, 'Records' => []]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $ids = array_map(fn (TransactionDto $t) => $t->entryId, iterator_to_array($client->bonline()->statement()->stream(
            new \DateTimeImmutable('2025-01-01'), new \DateTimeImmutable('2025-12-31'), 'GEL', 'GE00BG0000000000000001',
        ), false));

        $this->assertSame([1, 2, 3, 4, 5], $ids);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/4937843466048260/4'));
    }

    public function test_statement_stream_stops_on_an_empty_page_even_if_total_count_is_higher(): void
    {
        $base = 'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/';
        Http::fake([
            'account-test.bog.ge/*' => Http::response(['access_token' => 'bonline-token', 'expires_in' => 3600, 'token_type' => 'Bearer']),
            $base.'2025-01-01/2025-01-31' => Http::response(['Id' => 77, 'Count' => 1, 'TotalCount' => 10, 'Records' => [$this->makeTxFields(['EntryId' => 1])]]),
            $base.'77/2' => Http::response(['Id' => 77, 'Count' => 0, 'TotalCount' => 10, 'Records' => []]),
            $base.'*' => Http::response(['Records' => [$this->makeTxFields(['EntryId' => 99])]]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $all = iterator_to_array($client->bonline()->statement()->stream(new \DateTimeImmutable('2025-01-01'), new \DateTimeImmutable('2025-01-31'), 'GEL', 'GE00BG0000000000000001'), false);

        $this->assertCount(1, $all);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/77/3'));
    }

    public function test_statement_stream_single_page_makes_no_page_requests(): void
    {
        $base = 'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/';
        Http::fake([
            'account-test.bog.ge/*' => Http::response(['access_token' => 'bonline-token', 'expires_in' => 3600, 'token_type' => 'Bearer']),
            $base.'2025-01-01/2025-01-31' => Http::response(['Id' => 5, 'Count' => 2, 'TotalCount' => 2, 'Records' => [$this->makeTxFields(['EntryId' => 1]), $this->makeTxFields(['EntryId' => 2])]]),
            $base.'*' => Http::response(['Records' => [$this->makeTxFields(['EntryId' => 99])]]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $all = iterator_to_array($client->bonline()->statement()->stream(new \DateTimeImmutable('2025-01-01'), new \DateTimeImmutable('2025-01-31'), 'GEL', 'GE00BG0000000000000001'), false);

        $this->assertCount(2, $all);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/5/2'));
    }

    public function test_statement_stream_falls_back_to_count_without_total_count(): void
    {
        $base = 'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/';
        Http::fake([
            'account-test.bog.ge/*' => Http::response(['access_token' => 'bonline-token', 'expires_in' => 3600, 'token_type' => 'Bearer']),
            $base.'2025-01-01/2025-01-31' => Http::response(['Id' => 6, 'Count' => 2, 'Records' => [$this->makeTxFields(['EntryId' => 1])]]),
            $base.'6/2' => Http::response([$this->makeTxFields(['EntryId' => 2])]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $all = iterator_to_array($client->bonline()->statement()->stream(new \DateTimeImmutable('2025-01-01'), new \DateTimeImmutable('2025-01-31'), 'GEL', 'GE00BG0000000000000001'), false);

        $this->assertSame([1, 2], array_map(fn (TransactionDto $t) => $t->entryId, $all));
    }

    public function test_statement_page_reads_the_records_envelope(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/12345/2',
            Http::response(['Id' => 12346, 'Count' => 2, 'TotalCount' => 1002, 'Records' => [
                $this->makeTxFields(['EntryId' => 1001]), $this->makeTxFields(['EntryId' => 1002]),
            ]]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $page = $client->bonline()->statement()->page('GE00BG0000000000000001', 'GEL', 12345, 2);

        $this->assertSame([1001, 1002], array_map(fn (TransactionDto $t) => $t->entryId, $page));
    }

    public function test_summary(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/statement/summary/GE00BG0000000000000001/GEL/12345',
            Http::response([
                'GlobalSummary' => [
                    'OpeningBalance' => 1000.00,
                    'ClosingBalance' => 1234.56,
                    'DebitTurnover' => 500.00,
                    'CreditTurnover' => 734.56,
                ],
                'DailySummaries' => [],
            ]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $summary = $client->bonline()->summary()->get('GE00BG0000000000000001', 'GEL', 12345);

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('GlobalSummary', $summary);
        $this->assertEquals(1000.00, $summary['GlobalSummary']['OpeningBalance']);
        $this->assertEquals(1234.56, $summary['GlobalSummary']['ClosingBalance']);
    }

    public function test_summary_for_period(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/statement/v2/summary/GE00BG0000000000000001/GEL/2025-01-01/2025-01-31',
            Http::response([
                'GlobalSummary' => [
                    'OpeningBalance' => 1000.00,
                    'ClosingBalance' => 1234.56,
                ],
            ]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $summary = $client->bonline()->summary()->forPeriod('GE00BG0000000000000001', 'GEL', '2025-01-01', '2025-01-31');

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('GlobalSummary', $summary);
    }

    public function test_balance(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/accounts/GE00BG0000000000000001/GEL',
            Http::response(['AccountKey' => 11607228090, 'AvailableBalance' => 42631.22, 'CurrentBalance' => 42631.22, 'StatementBalance' => null]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $balance = $client->bonline()->balance()->get('GE00BG0000000000000001', 'GEL');

        $this->assertInstanceOf(BalanceDto::class, $balance);
        $this->assertSame('GE00BG0000000000000001', $balance->accountNumber);
        $this->assertSame('GEL', $balance->currency);
        $this->assertSame(42631.22, $balance->availableBalance);
        $this->assertSame(42631.22, $balance->currentBalance);
        $this->assertSame(0.0, $balance->blockedAmount);
        $this->assertSame(11607228090, $balance->accountKey);
        $this->assertNull($balance->statementBalance);

        Http::assertSent(fn ($r) => $r->url() === 'https://api-test.businessonline.ge/api/accounts/GE00BG0000000000000001/GEL');
    }

    public function test_balance_derives_the_blocked_amount_and_passes_the_statement_flag_as_query(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/accounts/GE00BG0000000000000001/USD*',
            Http::response(['AccountKey' => 7, 'AvailableBalance' => 900.0, 'CurrentBalance' => 1000.0, 'StatementBalance' => 950.5]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $balance = $client->bonline()->balance()->get('GE00BG0000000000000001', 'USD', initStatementBalance: true);

        $this->assertSame(100.0, $balance->blockedAmount);
        $this->assertSame(950.5, $balance->statementBalance);
        $this->assertSame('USD', $balance->currency);
        Http::assertSent(fn ($r) => $r->url() === 'https://api-test.businessonline.ge/api/accounts/GE00BG0000000000000001/USD?initStatementBalance=true');
        Http::assertNotSent(fn ($r) => str_ends_with(parse_url($r->url(), PHP_URL_PATH) ?? '', '/1') || str_ends_with(parse_url($r->url(), PHP_URL_PATH) ?? '', '/0'));
    }

    public function test_check_inn(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/accounts/checkInn',
            Http::response(['Name' => 'Test Company', 'Inn' => '123456789']),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->bonline()->accounts()->checkInn('123456789');

        $this->assertIsArray($result);
        $this->assertSame('Test Company', $result['Name']);
    }

    public function test_currency_rates_commercial(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/rates/commercial/USD',
            Http::response(['Buy' => 2.65, 'Sell' => 2.72]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $rate = $client->bonline()->currencyRates()->commercial('USD');

        $this->assertInstanceOf(CurrencyRateDto::class, $rate);
        $this->assertSame(2.65, $rate->buyRate);
        $this->assertSame(2.72, $rate->sellRate);
    }

    public function test_currency_rates_list(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'bonline-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.businessonline.ge/api/rates/commercial/USD' => Http::response(['Buy' => 2.65, 'Sell' => 2.72]),
            'api-test.businessonline.ge/api/rates/commercial/EUR' => Http::response(['Buy' => 2.88, 'Sell' => 2.96]),
            'api-test.businessonline.ge/api/rates/commercial/GBP' => Http::response(['Buy' => 3.30, 'Sell' => 3.40]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $rates = $client->bonline()->currencyRates()->list();

        $this->assertCount(3, $rates);
        $this->assertInstanceOf(CurrencyRateDto::class, $rates[0]);
        $this->assertSame(2.65, $rates[0]->buyRate);
        $this->assertSame(2.88, $rates[1]->buyRate);
        $this->assertSame(3.30, $rates[2]->buyRate);
    }

    public function test_currency_rates_cross_rate(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/rates/commercial/USD/EUR',
            Http::response('1.199474'),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $rate = $client->bonline()->currencyRates()->crossRate('USD', 'EUR');

        $this->assertSame(1.199474, $rate);
    }

    public function test_currency_rates_nbg(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/rates/nbg/USD',
            Http::response('2.6938'),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $rate = $client->bonline()->currencyRates()->nbg('USD');

        $this->assertSame(2.6938, $rate);
    }

    public function test_requisites(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/requisites/GE00BG0000000000000001/GEL',
            Http::response([
                'BankName' => 'Bank of Georgia',
                'SwiftCode' => 'BAGAGE22',
                'BranchCode' => '001',
                'IBAN' => 'GE00BG0000000000000001',
            ]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $requisites = $client->bonline()->requisites()->get('GE00BG0000000000000001', 'GEL');

        $this->assertIsArray($requisites);
        $this->assertSame('Bank of Georgia', $requisites['BankName']);
        $this->assertSame('BAGAGE22', $requisites['SwiftCode']);
    }
}
