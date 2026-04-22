<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\BogClient;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\BalanceDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\CurrencyRateDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\StatementPageDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\TransactionDto;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

final class BonlineTest extends TestCase
{
    private function fakeTokenAndEndpoint(string $urlPattern, $response): void
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

    public function test_statement_for_period(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/2025-01-01/2025-01-31/0/1/100',
            Http::response([
                'Id' => 12345,
                'RecordCount' => 1,
                'Records' => [
                    [
                        'Id' => 999,
                        'EntryDate' => '2025-01-15T12:34:56',
                        'EntryDocumentNumber' => '0001',
                        'EntryAccountNumber' => 'GE00BG0000000000000001',
                        'EntryAmountDebit' => 0.0,
                        'EntryAmountCredit' => 150.25,
                        'EntryAmountBase' => 150.25,
                        'EntryAmount' => 150.25,
                        'EntryComment' => 'Test payment',
                        'DocumentProductGroup' => 'TRF',
                        'DocumentValueDate' => '2025-01-15T00:00:00',
                        'DocumentOperationCode' => 'IN',
                        'DocumentOperationType' => 'TRANSFER',
                        'DocumentPayerName' => 'Payer',
                        'DocumentPayerInn' => '123456789',
                        'DocumentPayerAccount' => 'GE00BG0000000000000002',
                        'DocumentBeneficiaryName' => 'Beneficiary',
                        'DocumentBeneficiaryInn' => '987654321',
                        'DocumentBeneficiaryAccount' => 'GE00BG0000000000000001',
                        'DocumentBeneficiaryBankCode' => 'BAGAGE22',
                        'DocumentBeneficiaryBankName' => 'Bank of Georgia',
                        'DocumentNomination' => 'Payment',
                        'DocumentInformation' => '',
                        'DocumentAdditionalInformation' => '',
                        'DocumentSenderInstitution' => '',
                        'DocumentIntermediaryInstitution' => '',
                        'DocumentReceiverInstitution' => '',
                        'DocumentPayeeInn' => '',
                    ],
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
        $this->assertSame(1, $result->recordCount);
        $this->assertCount(1, $result->records);
        $this->assertInstanceOf(TransactionDto::class, $result->records[0]);
        $this->assertSame(999, $result->records[0]->id);
        $this->assertSame(150.25, $result->records[0]->entryAmountCredit);
        $this->assertSame('Test payment', $result->records[0]->entryComment);
    }

    public function test_statement_paging(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/12345/2/1',
            Http::response([
                'Id' => 12345,
                'RecordCount' => 0,
                'Records' => [],
            ]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->bonline()->statement()->page('GE00BG0000000000000001', 'GEL', 12345, 2);

        $this->assertInstanceOf(StatementPageDto::class, $result);
        $this->assertSame('12345', $result->id);
    }

    public function test_statement_stream_auto_paginates(): void
    {
        $txFields = ['EntryDate' => '', 'EntryDocumentNumber' => '', 'EntryAccountNumber' => '', 'EntryAmountDebit' => 0.0, 'EntryAmountCredit' => 10.0, 'EntryAmountBase' => 10.0, 'EntryAmount' => 10.0, 'EntryComment' => '', 'DocumentProductGroup' => '', 'DocumentValueDate' => '', 'DocumentOperationCode' => '', 'DocumentOperationType' => '', 'DocumentPayerName' => '', 'DocumentPayerInn' => '', 'DocumentPayerAccount' => '', 'DocumentBeneficiaryName' => '', 'DocumentBeneficiaryInn' => '', 'DocumentBeneficiaryAccount' => '', 'DocumentBeneficiaryBankCode' => '', 'DocumentBeneficiaryBankName' => '', 'DocumentNomination' => '', 'DocumentInformation' => '', 'DocumentAdditionalInformation' => '', 'DocumentSenderInstitution' => '', 'DocumentIntermediaryInstitution' => '', 'DocumentReceiverInstitution' => '', 'DocumentPayeeInn' => ''];

        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'bonline-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/2025-01-01/2025-01-31/0/1/100' => Http::response([
                'Id' => 100,
                'RecordCount' => 2,
                'Records' => [array_merge(['Id' => 1], $txFields)],
            ]),
            'api-test.businessonline.ge/api/statement/v2/GE00BG0000000000000001/GEL/100/2/1' => Http::response([
                'Id' => 100,
                'RecordCount' => 2,
                'Records' => [array_merge(['Id' => 2], $txFields)],
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
        $this->assertSame(1, $transactions[0]->id);
        $this->assertSame(2, $transactions[1]->id);
    }

    public function test_today_activities(): void
    {
        $txFields = ['EntryDate' => '2025-01-20T10:00:00', 'EntryDocumentNumber' => '', 'EntryAccountNumber' => '', 'EntryAmountDebit' => 100.0, 'EntryAmountCredit' => 0.0, 'EntryAmountBase' => 100.0, 'EntryAmount' => 100.0, 'EntryComment' => 'Today', 'DocumentProductGroup' => '', 'DocumentValueDate' => '', 'DocumentOperationCode' => '', 'DocumentOperationType' => '', 'DocumentPayerName' => '', 'DocumentPayerInn' => '', 'DocumentPayerAccount' => '', 'DocumentBeneficiaryName' => '', 'DocumentBeneficiaryInn' => '', 'DocumentBeneficiaryAccount' => '', 'DocumentBeneficiaryBankCode' => '', 'DocumentBeneficiaryBankName' => '', 'DocumentNomination' => '', 'DocumentInformation' => '', 'DocumentAdditionalInformation' => '', 'DocumentSenderInstitution' => '', 'DocumentIntermediaryInstitution' => '', 'DocumentReceiverInstitution' => '', 'DocumentPayeeInn' => ''];

        $this->fakeTokenAndEndpoint(
            'api-test.businessonline.ge/api/documents/v2/todayactivities/GE00BG0000000000000001/GEL',
            Http::response(['Records' => [array_merge(['Id' => 555], $txFields)]]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $activities = $client->bonline()->todayActivities()->get('GE00BG0000000000000001', 'GEL');

        $this->assertCount(1, $activities);
        $this->assertInstanceOf(TransactionDto::class, $activities[0]);
        $this->assertSame(555, $activities[0]->id);
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
            'api-test.businessonline.ge/api/accounts/GE00BG0000000000000001/GEL/0',
            Http::response([
                'AccountNumber' => 'GE00BG0000000000000001',
                'Currency' => 'GEL',
                'AvailableBalance' => 1234.56,
                'CurrentBalance' => 1234.56,
                'BlockedAmount' => 0.0,
            ]),
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $balance = $client->bonline()->balance()->get('GE00BG0000000000000001', 'GEL');

        $this->assertInstanceOf(BalanceDto::class, $balance);
        $this->assertSame('GE00BG0000000000000001', $balance->accountNumber);
        $this->assertSame(1234.56, $balance->availableBalance);
        $this->assertSame(0.0, $balance->blockedAmount);
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
