<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\BogClient;
use GiorgiGrdzelidze\BogSdk\BogId\Dto\BogIdTokenDto;
use GiorgiGrdzelidze\BogSdk\BogId\Dto\BogIdUserDto;
use GiorgiGrdzelidze\BogSdk\BogId\Enums\BogIdClaim;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogIdException;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

final class BogIdTest extends TestCase
{
    public function test_redirect_url_contains_all_params(): void
    {
        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $url = $client->bogId()->redirectUrl(
            [BogIdClaim::FPI->value, BogIdClaim::CI->value],
            'https://example.com/callback',
            'random-state-123',
        );

        $this->assertStringContainsString('client_id=test-bogid-id', $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('scope=openid+FPI+CI', $url);
        $this->assertStringContainsString('redirect_uri=', $url);
        $this->assertStringContainsString('state=random-state-123', $url);
        $this->assertStringStartsWith('https://account-test.bog.ge/auth/realms/bog/protocol/openid-connect/auth?', $url);
    }

    public function test_exchange_code(): void
    {
        Http::fake([
            'account-test.bog.ge/auth/realms/bog/protocol/openid-connect/token' => Http::response([
                'access_token' => 'bogid-access-token',
                'id_token' => 'bogid-id-token',
                'expires_in' => 300,
                'refresh_token' => 'bogid-refresh-token',
                'token_type' => 'Bearer',
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $tokenDto = $client->bogId()->exchangeCode('auth-code-abc', 'https://example.com/callback');

        $this->assertInstanceOf(BogIdTokenDto::class, $tokenDto);
        $this->assertSame('bogid-access-token', $tokenDto->accessToken);
        $this->assertSame('bogid-id-token', $tokenDto->idToken);
        $this->assertSame(300, $tokenDto->expiresIn);
    }

    public function test_exchange_code_throws_on_failure(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response('Server Error', 500),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);

        $this->expectException(BogIdException::class);
        $this->expectExceptionMessage('BOG-ID token exchange failed');

        $client->bogId()->exchangeCode('bad-code', 'https://example.com/callback');
    }

    public function test_userinfo(): void
    {
        Http::fake([
            'account-test.bog.ge/auth/realms/bog/protocol/openid-connect/userinfo' => Http::response([
                'sub' => 'user-uuid-123',
                'name' => 'Test User',
                'given_name' => 'Test',
                'family_name' => 'User',
                'email' => 'test@example.com',
                'email_verified' => true,
                'phone_number' => '+995599000000',
                'personal_number' => '01001012345',
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $user = $client->bogId()->userinfo('bogid-access-token');

        $this->assertInstanceOf(BogIdUserDto::class, $user);
        $this->assertSame('user-uuid-123', $user->sub);
        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertTrue($user->emailVerified);
        $this->assertSame('01001012345', $user->personalNumber);
    }

    public function test_bog_id_claim_enum(): void
    {
        $this->assertSame('FPI', BogIdClaim::FPI->value);
        $this->assertSame('Full personal info', BogIdClaim::FPI->description());
        $this->assertSame('Contact info', BogIdClaim::CI->description());
    }
}
