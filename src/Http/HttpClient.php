<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Http;

use GiorgiGrdzelidze\BogSdk\Auth\TokenManager;
use GiorgiGrdzelidze\BogSdk\Contracts\HttpClientContract;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogHttpException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

/**
 * Token-aware HTTP client that automatically attaches Bearer tokens
 * and retries on 401 (token expired) responses.
 */
final class HttpClient implements HttpClientContract
{
    /**
     * @param  array<string, mixed>  $config  Resolved bog-sdk config array.
     */
    public function __construct(
        private readonly Factory $http,
        private readonly TokenManager $tokens,
        private readonly array $config,
    ) {}

    /** {@inheritDoc} */
    public function get(string $domain, string $url, array $query = []): array
    {
        return $this->request('get', $domain, $url, $query);
    }

    /** {@inheritDoc} */
    public function post(string $domain, string $url, array $body = []): array
    {
        return $this->request('post', $domain, $url, $body);
    }

    /** {@inheritDoc} */
    public function put(string $domain, string $url, array $body = []): array
    {
        return $this->request('put', $domain, $url, $body);
    }

    /** {@inheritDoc} */
    public function patch(string $domain, string $url, array $body = []): array
    {
        return $this->request('patch', $domain, $url, $body);
    }

    /** {@inheritDoc} */
    public function delete(string $domain, string $url, array $body = []): array
    {
        return $this->request('delete', $domain, $url, $body);
    }

    /** {@inheritDoc} */
    public function getRaw(string $domain, string $url, array $query = []): string
    {
        $token = $this->tokens->for($domain);
        $response = $this->send('get', $token, $url, $query);

        if ($response->status() === 401) {
            $this->tokens->forget($domain);
            $token = $this->tokens->for($domain);
            $response = $this->send('get', $token, $url, $query);
        }

        if ($response->failed()) {
            throw BogHttpException::fromResponse($response, $url);
        }

        return $response->body();
    }

    /**
     * Send an authenticated request, retry once on 401, and return parsed JSON.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws BogHttpException
     */
    private function request(string $method, string $domain, string $url, array $data = []): array
    {
        $token = $this->tokens->for($domain);
        $response = $this->send($method, $token, $url, $data);

        if ($response->status() === 401) {
            $this->tokens->forget($domain);
            $token = $this->tokens->for($domain);
            $response = $this->send($method, $token, $url, $data);
        }

        if ($response->failed()) {
            throw BogHttpException::fromResponse($response, $url);
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    /**
     * Execute the raw HTTP request using Laravel's HTTP client.
     *
     * @param  array<string, mixed>  $data
     */
    private function send(string $method, string $token, string $url, array $data): Response
    {
        $pending = $this->pending($token);

        return match ($method) {
            'get' => $pending->get($url, $data),
            'post' => $pending->post($url, $data),
            'put' => $pending->put($url, $data),
            'patch' => $pending->patch($url, $data),
            'delete' => $pending->delete($url, $data),
            default => $pending->send($method, $url, ['json' => $data]),
        };
    }

    /**
     * Build a PendingRequest pre-configured with token, headers, timeout, and retry.
     */
    private function pending(string $token): PendingRequest
    {
        $httpConfig = $this->config['http'] ?? [];

        return $this->http
            ->withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout((int) ($httpConfig['timeout'] ?? 15))
            ->retry(
                (int) ($httpConfig['retry_times'] ?? 2),
                (int) ($httpConfig['retry_sleep_ms'] ?? 250),
                throw: false,
            );
    }

    /**
     * Get the underlying Laravel HTTP factory for advanced/custom requests.
     */
    public function raw(): Factory
    {
        return $this->http;
    }

    /**
     * Get the token manager instance for manual token operations.
     */
    public function tokens(): TokenManager
    {
        return $this->tokens;
    }
}
