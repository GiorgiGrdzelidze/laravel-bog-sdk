<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\Auth\TokenManager;
use GiorgiGrdzelidze\BogSdk\BogClient;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;

final class ServiceProviderTest extends TestCase
{
    public function test_bog_client_is_registered_as_singleton(): void
    {
        $client1 = $this->app->make(BogClient::class);
        $client2 = $this->app->make(BogClient::class);

        $this->assertInstanceOf(BogClient::class, $client1);
        $this->assertSame($client1, $client2);
    }

    public function test_bog_alias_resolves_to_bog_client(): void
    {
        $client = $this->app->make('bog');

        $this->assertInstanceOf(BogClient::class, $client);
    }

    public function test_token_manager_is_registered_as_singleton(): void
    {
        $tm1 = $this->app->make(TokenManager::class);
        $tm2 = $this->app->make(TokenManager::class);

        $this->assertInstanceOf(TokenManager::class, $tm1);
        $this->assertSame($tm1, $tm2);
    }

    public function test_http_client_is_registered_as_singleton(): void
    {
        $hc1 = $this->app->make(HttpClient::class);
        $hc2 = $this->app->make(HttpClient::class);

        $this->assertInstanceOf(HttpClient::class, $hc1);
        $this->assertSame($hc1, $hc2);
    }

    public function test_config_is_merged(): void
    {
        $this->assertNotNull(config('bog-sdk.http.timeout'));
        $this->assertNotNull(config('bog-sdk.bonline.base_url'));
        $this->assertNotNull(config('bog-sdk.payments.base_url'));
    }
}
