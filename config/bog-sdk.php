<?php

declare(strict_types=1);

return [
    'http' => [
        'timeout' => (int) env('BOG_HTTP_TIMEOUT', 15),
        'retry_times' => (int) env('BOG_HTTP_RETRY_TIMES', 2),
        'retry_sleep_ms' => (int) env('BOG_HTTP_RETRY_SLEEP_MS', 250),
    ],

    'token_cache' => [
        'store' => env('BOG_TOKEN_CACHE_STORE', null),
        'key_prefix' => env('BOG_TOKEN_CACHE_PREFIX', 'bog-sdk:token:'),
        'safety_ttl' => (int) env('BOG_TOKEN_CACHE_SAFETY_TTL', 60),
    ],

    'bonline' => [
        'base_url' => env('BOG_BONLINE_BASE_URL', 'https://api.businessonline.ge/api'),
        'token_url' => env('BOG_BONLINE_TOKEN_URL', 'https://account.bog.ge/auth/realms/bog/protocol/openid-connect/token'),
        'client_id' => env('BOG_BONLINE_CLIENT_ID'),
        'client_secret' => env('BOG_BONLINE_CLIENT_SECRET'),
        'accounts' => array_filter(explode(',', env('BOG_BONLINE_ACCOUNTS', ''))),
        'default_account' => env('BOG_BONLINE_DEFAULT_ACCOUNT'),
        'default_currency' => env('BOG_BONLINE_DEFAULT_CURRENCY', 'GEL'),
        'currencies' => array_filter(explode(',', env('BOG_BONLINE_CURRENCIES', ''))),
    ],

    'payments' => [
        'base_url' => env('BOG_PAYMENTS_BASE_URL', 'https://api.bog.ge/payments/v1'),
        'token_url' => env('BOG_PAYMENTS_TOKEN_URL', 'https://oauth2.bog.ge/auth/realms/bog/protocol/openid-connect/token'),
        'client_id' => env('BOG_PAYMENTS_CLIENT_ID'),
        'client_secret' => env('BOG_PAYMENTS_CLIENT_SECRET'),
        'callback_public_key_path' => env('BOG_PAYMENTS_CALLBACK_KEY_PATH', storage_path('app/bog-sdk/bog-payments-callback.pem')),
    ],

    'billing' => [
        'base_url' => env('BOG_BILLING_BASE_URL', 'https://api.bog.ge/billing/v1'),
        'token_url' => env('BOG_BILLING_TOKEN_URL', 'https://oauth2.bog.ge/auth/realms/bog/protocol/openid-connect/token'),
        'auth_type' => env('BOG_BILLING_AUTH', 'oauth2'),
        'api_key' => env('BOG_BILLING_API_KEY'),
        'hmac_secret' => env('BOG_BILLING_HMAC_SECRET'),
        'client_id' => env('BOG_BILLING_CLIENT_ID'),
        'client_secret' => env('BOG_BILLING_CLIENT_SECRET'),
    ],

    'ipay' => [
        'base_url' => env('BOG_IPAY_BASE_URL', 'https://ipay.ge/opay/api/v1'),
        'token_url' => env('BOG_IPAY_TOKEN_URL', 'https://ipay.ge/opay/api/v1/oauth2/token'),
        'client_id' => env('BOG_IPAY_CLIENT_ID'),
        'client_secret' => env('BOG_IPAY_CLIENT_SECRET'),
    ],

    'installment' => [
        'base_url' => env('BOG_INSTALLMENT_BASE_URL', 'https://api.bog.ge/installment/v1'),
        'token_url' => env('BOG_INSTALLMENT_TOKEN_URL', 'https://oauth2.bog.ge/auth/realms/bog/protocol/openid-connect/token'),
        'client_id' => env('BOG_INSTALLMENT_CLIENT_ID'),
        'client_secret' => env('BOG_INSTALLMENT_CLIENT_SECRET'),
        'shop_id' => env('BOG_INSTALLMENT_SHOP_ID'),
    ],

    'bog_id' => [
        'issuer' => env('BOG_ID_ISSUER', 'https://account.bog.ge/auth/realms/bog'),
        'client_id' => env('BOG_ID_CLIENT_ID'),
        'client_secret' => env('BOG_ID_CLIENT_SECRET'),
        'redirect_uri' => env('BOG_ID_REDIRECT_URI'),
    ],

    'open_banking' => [
        'base_url' => env('BOG_OB_BASE_URL', 'https://api.bog.ge'),
        'token_url' => env('BOG_OB_TOKEN_URL', 'https://oauth2.bog.ge/auth/realms/bog/protocol/openid-connect/token'),
        'client_id' => env('BOG_OB_CLIENT_ID'),
        'client_secret' => env('BOG_OB_CLIENT_SECRET'),
    ],
];
