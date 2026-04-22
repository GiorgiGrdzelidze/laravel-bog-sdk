<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline;

use GiorgiGrdzelidze\BogSdk\Bonline\Endpoints\AccountsEndpoint;
use GiorgiGrdzelidze\BogSdk\Bonline\Endpoints\BalanceEndpoint;
use GiorgiGrdzelidze\BogSdk\Bonline\Endpoints\CurrencyRatesEndpoint;
use GiorgiGrdzelidze\BogSdk\Bonline\Endpoints\RequisitesEndpoint;
use GiorgiGrdzelidze\BogSdk\Bonline\Endpoints\StatementEndpoint;
use GiorgiGrdzelidze\BogSdk\Bonline\Endpoints\SummaryEndpoint;
use GiorgiGrdzelidze\BogSdk\Bonline\Endpoints\TodayActivitiesEndpoint;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;

/**
 * Business Online (Bonline) API client.
 *
 * Provides lazy-loaded access to account balance, statements,
 * today's activities, currency rates, summary, and requisites endpoints.
 */
final class BonlineClient
{
    private ?AccountsEndpoint $accountsEndpoint = null;

    private ?StatementEndpoint $statementEndpoint = null;

    private ?TodayActivitiesEndpoint $todayActivitiesEndpoint = null;

    private ?SummaryEndpoint $summaryEndpoint = null;

    private ?BalanceEndpoint $balanceEndpoint = null;

    private ?CurrencyRatesEndpoint $currencyRatesEndpoint = null;

    private ?RequisitesEndpoint $requisitesEndpoint = null;

    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    /**
     * Get the accounts endpoint (INN check).
     */
    public function accounts(): AccountsEndpoint
    {
        return $this->accountsEndpoint ??= new AccountsEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the statement endpoint for account transaction history.
     */
    public function statement(): StatementEndpoint
    {
        return $this->statementEndpoint ??= new StatementEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get today's activities endpoint for intraday transactions.
     */
    public function todayActivities(): TodayActivitiesEndpoint
    {
        return $this->todayActivitiesEndpoint ??= new TodayActivitiesEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the summary endpoint for statement balance summaries.
     */
    public function summary(): SummaryEndpoint
    {
        return $this->summaryEndpoint ??= new SummaryEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the balance endpoint for account balance queries.
     */
    public function balance(): BalanceEndpoint
    {
        return $this->balanceEndpoint ??= new BalanceEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the currency rates endpoint for commercial and NBG rates.
     */
    public function currencyRates(): CurrencyRatesEndpoint
    {
        return $this->currencyRatesEndpoint ??= new CurrencyRatesEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the requisites endpoint for bank/SWIFT details.
     */
    public function requisites(): RequisitesEndpoint
    {
        return $this->requisitesEndpoint ??= new RequisitesEndpoint($this->http, $this->baseUrl);
    }
}
