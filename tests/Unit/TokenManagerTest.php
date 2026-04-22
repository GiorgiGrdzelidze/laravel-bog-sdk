<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\Auth\TokenManager;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogAuthenticationException;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

final class TokenManagerTest extends TestCase
{
    public function test_fetches_and_caches_token(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'test-token-abc',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $tm = $this->app->make(TokenManager::class);
        $token = $tm->for('bonline');

        $this->assertSame('test-token-abc', $token);

        // Second call should use cache (no additional HTTP call)
        $token2 = $tm->for('bonline');
        $this->assertSame('test-token-abc', $token2);

        Http::assertSentCount(1);
    }

    public function test_forget_clears_cache(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'test-token-abc',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $tm = $this->app->make(TokenManager::class);
        $tm->for('bonline');
        $tm->forget('bonline');
        $tm->for('bonline');

        Http::assertSentCount(2);
    }

    public function test_throws_on_missing_credentials(): void
    {
        $this->app['config']->set('bog-sdk.bonline.client_id', '');

        $tm = $this->app->make(TokenManager::class);

        $this->expectException(BogAuthenticationException::class);
        $this->expectExceptionMessage("Missing OAuth credentials for domain 'bonline'");

        $tm->for('bonline');
    }

    public function test_throws_on_failed_token_request(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response('Unauthorized', 401),
        ]);

        $tm = $this->app->make(TokenManager::class);

        $this->expectException(BogAuthenticationException::class);
        $this->expectExceptionMessage("OAuth token request failed for 'bonline'");

        $tm->for('bonline');
    }

    public function test_throws_when_response_missing_access_token(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response(['error' => 'invalid_grant']),
        ]);

        $tm = $this->app->make(TokenManager::class);

        $this->expectException(BogAuthenticationException::class);
        $this->expectExceptionMessage('did not contain access_token');

        $tm->for('bonline');
    }
}
