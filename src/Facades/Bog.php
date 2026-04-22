<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Facades;

use GiorgiGrdzelidze\BogSdk\BogClient;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \GiorgiGrdzelidze\BogSdk\Bonline\BonlineClient bonline()
 * @method static \GiorgiGrdzelidze\BogSdk\Payments\PaymentsClient payments()
 * @method static \GiorgiGrdzelidze\BogSdk\Billing\BillingClient billing()
 * @method static \GiorgiGrdzelidze\BogSdk\IPay\IPayClient ipay()
 * @method static \GiorgiGrdzelidze\BogSdk\Installment\InstallmentClient installment()
 * @method static \GiorgiGrdzelidze\BogSdk\BogId\BogIdClient bogId()
 * @method static \GiorgiGrdzelidze\BogSdk\OpenBanking\OpenBankingClient openBanking()
 *
 * Bonline sub-endpoints:
 * - Bog::bonline()->accounts()->checkInn($inn)
 * - Bog::bonline()->balance()->get($iban, $currency, $initStatementBalance)
 * - Bog::bonline()->statement()->forPeriod($acct, $currency, $from, $to, ...)
 * - Bog::bonline()->statement()->page($acct, $currency, $statementId, $page, ...)
 * - Bog::bonline()->todayActivities()->get($iban, $currency)
 * - Bog::bonline()->summary()->get($iban, $currency, $statementId)
 * - Bog::bonline()->summary()->forPeriod($acct, $currency, $from, $to, ...)
 * - Bog::bonline()->currencyRates()->commercial($currency)
 * - Bog::bonline()->currencyRates()->crossRate($sell, $buy)
 * - Bog::bonline()->currencyRates()->nbg($currency)
 * - Bog::bonline()->requisites()->get($iban, $currency)
 *
 * @see BogClient
 */
final class Bog extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return 'bog';
    }
}
