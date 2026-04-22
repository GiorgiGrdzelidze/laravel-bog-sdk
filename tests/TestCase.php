<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests;

use GiorgiGrdzelidze\BogSdk\BogSdkServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            BogSdkServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');

        $app['config']->set('bog-sdk.http.timeout', 10);
        $app['config']->set('bog-sdk.http.retry_times', 0);
        $app['config']->set('bog-sdk.http.retry_sleep_ms', 0);

        $app['config']->set('bog-sdk.bonline.base_url', 'https://api-test.businessonline.ge/api');
        $app['config']->set('bog-sdk.bonline.token_url', 'https://account-test.bog.ge/auth/realms/bog/protocol/openid-connect/token');
        $app['config']->set('bog-sdk.bonline.client_id', 'test-bonline-id');
        $app['config']->set('bog-sdk.bonline.client_secret', 'test-bonline-secret');

        $app['config']->set('bog-sdk.payments.base_url', 'https://api-test.bog.ge/payments/v1');
        $app['config']->set('bog-sdk.payments.token_url', 'https://oauth2-test.bog.ge/auth/realms/bog/protocol/openid-connect/token');
        $app['config']->set('bog-sdk.payments.client_id', 'test-payments-id');
        $app['config']->set('bog-sdk.payments.client_secret', 'test-payments-secret');

        $app['config']->set('bog-sdk.ipay.base_url', 'https://ipay-test.bog.ge/opay/api/v1');
        $app['config']->set('bog-sdk.ipay.token_url', 'https://ipay-test.bog.ge/opay/api/v1/oauth2/token');
        $app['config']->set('bog-sdk.ipay.client_id', 'test-ipay-id');
        $app['config']->set('bog-sdk.ipay.client_secret', 'test-ipay-secret');

        $app['config']->set('bog-sdk.installment.base_url', 'https://api-test.bog.ge/installment/v1');
        $app['config']->set('bog-sdk.installment.token_url', 'https://oauth2-test.bog.ge/auth/realms/bog/protocol/openid-connect/token');
        $app['config']->set('bog-sdk.installment.client_id', 'test-installment-id');
        $app['config']->set('bog-sdk.installment.client_secret', 'test-installment-secret');
        $app['config']->set('bog-sdk.installment.shop_id', 'test-shop-id');

        $app['config']->set('bog-sdk.billing.base_url', 'https://api-test.bog.ge/billing/v1');
        $app['config']->set('bog-sdk.billing.token_url', 'https://oauth2-test.bog.ge/auth/realms/bog/protocol/openid-connect/token');
        $app['config']->set('bog-sdk.billing.auth_type', 'oauth2');
        $app['config']->set('bog-sdk.billing.client_id', 'test-billing-id');
        $app['config']->set('bog-sdk.billing.client_secret', 'test-billing-secret');

        $app['config']->set('bog-sdk.bog_id.issuer', 'https://account-test.bog.ge/auth/realms/bog');
        $app['config']->set('bog-sdk.bog_id.client_id', 'test-bogid-id');
        $app['config']->set('bog-sdk.bog_id.client_secret', 'test-bogid-secret');

        $app['config']->set('bog-sdk.open_banking.base_url', 'https://api-test.bog.ge');
        $app['config']->set('bog-sdk.open_banking.token_url', 'https://oauth2-test.bog.ge/auth/realms/bog/protocol/openid-connect/token');
        $app['config']->set('bog-sdk.open_banking.client_id', 'test-ob-id');
        $app['config']->set('bog-sdk.open_banking.client_secret', 'test-ob-secret');
    }
}
