<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\Contracts\HttpClientContract;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogHttpException;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

final class HttpClientTest extends TestCase
{
    private function fakeTokenAndApi(string $urlPattern, array $response, int $status = 200): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            $urlPattern => Http::response($response, $status),
        ]);
    }

    public function test_implements_contract(): void
    {
        $client = $this->app->make(HttpClient::class);
        $this->assertInstanceOf(HttpClientContract::class, $client);
    }

    public function test_get_request(): void
    {
        $this->fakeTokenAndApi('api-test.businessonline.ge/api/test', ['result' => 'ok']);

        $client = $this->app->make(HttpClient::class);
        $result = $client->get('bonline', 'https://api-test.businessonline.ge/api/test');

        $this->assertSame('ok', $result['result']);
        Http::assertSent(fn ($r) => $r->method() === 'GET');
    }

    public function test_post_request(): void
    {
        $this->fakeTokenAndApi('api-test.businessonline.ge/api/test', ['created' => true]);

        $client = $this->app->make(HttpClient::class);
        $result = $client->post('bonline', 'https://api-test.businessonline.ge/api/test', ['data' => 'value']);

        $this->assertTrue($result['created']);
        Http::assertSent(fn ($r) => $r->method() === 'POST');
    }

    public function test_put_request(): void
    {
        $this->fakeTokenAndApi('api-test.businessonline.ge/api/test', ['updated' => true]);

        $client = $this->app->make(HttpClient::class);
        $result = $client->put('bonline', 'https://api-test.businessonline.ge/api/test', ['data' => 'value']);

        $this->assertTrue($result['updated']);
        Http::assertSent(fn ($r) => $r->method() === 'PUT');
    }

    public function test_patch_request(): void
    {
        $this->fakeTokenAndApi('api-test.businessonline.ge/api/test', ['patched' => true]);

        $client = $this->app->make(HttpClient::class);
        $result = $client->patch('bonline', 'https://api-test.businessonline.ge/api/test', ['field' => 'new']);

        $this->assertTrue($result['patched']);
        Http::assertSent(fn ($r) => $r->method() === 'PATCH');
    }

    public function test_delete_request(): void
    {
        $this->fakeTokenAndApi('api-test.businessonline.ge/api/test', ['deleted' => true]);

        $client = $this->app->make(HttpClient::class);
        $result = $client->delete('bonline', 'https://api-test.businessonline.ge/api/test');

        $this->assertTrue($result['deleted']);
        Http::assertSent(fn ($r) => $r->method() === 'DELETE');
    }

    public function test_retries_on_401(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'new-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.businessonline.ge/api/test' => Http::sequence()
                ->push(null, 401)
                ->push(['retry_success' => true], 200),
        ]);

        $client = $this->app->make(HttpClient::class);
        $result = $client->get('bonline', 'https://api-test.businessonline.ge/api/test');

        $this->assertTrue($result['retry_success']);
    }

    public function test_throws_on_http_error(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.businessonline.ge/api/test' => Http::response(['error' => 'not found'], 404),
        ]);

        $client = $this->app->make(HttpClient::class);

        $this->expectException(BogHttpException::class);
        $client->get('bonline', 'https://api-test.businessonline.ge/api/test');
    }

    public function test_returns_empty_array_for_non_json_response(): void
    {
        Http::fake([
            'account-test.bog.ge/*' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.businessonline.ge/api/test' => Http::response('OK', 200),
        ]);

        $client = $this->app->make(HttpClient::class);
        $result = $client->get('bonline', 'https://api-test.businessonline.ge/api/test');

        $this->assertSame([], $result);
    }

    public function test_raw_returns_http_factory(): void
    {
        $client = $this->app->make(HttpClient::class);
        $this->assertInstanceOf(Factory::class, $client->raw());
    }
}
