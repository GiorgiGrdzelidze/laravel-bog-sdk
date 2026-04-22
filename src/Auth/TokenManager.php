<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Auth;

use GiorgiGrdzelidze\BogSdk\Auth\Dto\AccessToken;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogAuthenticationException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory;

/**
 * Manages OAuth2 client-credentials tokens per BOG API domain.
 *
 * Tokens are cached in both a runtime array (per-request) and the
 * Laravel cache store (cross-request), with a configurable safety TTL
 * to refresh tokens before they expire.
 */
final class TokenManager
{
    /** @var array<string, string> */
    private array $runtimeCache = [];

    /**
     * @param  array<string, mixed>  $config  Resolved bog-sdk config array.
     */
    public function __construct(
        private readonly CacheRepository $cache,
        private readonly Factory $http,
        private readonly array $config,
    ) {}

    /**
     * Get a valid access token for the given API domain.
     *
     * Returns a cached token if available, otherwise fetches a new one
     * from the OAuth2 token endpoint.
     *
     * @throws BogAuthenticationException
     */
    public function for(string $domain): string
    {
        if (isset($this->runtimeCache[$domain])) {
            return $this->runtimeCache[$domain];
        }

        $prefix = (string) ($this->config['token_cache']['key_prefix'] ?? 'bog-sdk:token:');
        $key = $prefix.$domain;

        $token = $this->cache->get($key);
        if (is_string($token) && $token !== '') {
            $this->runtimeCache[$domain] = $token;

            return $token;
        }

        $accessToken = $this->fetch($domain);
        $safetyTtl = (int) ($this->config['token_cache']['safety_ttl'] ?? 60);
        $ttl = max($accessToken->expiresIn - $safetyTtl, 30);

        $this->cache->put($key, $accessToken->accessToken, $ttl);
        $this->runtimeCache[$domain] = $accessToken->accessToken;

        return $accessToken->accessToken;
    }

    /**
     * Remove the cached token for the given domain (both runtime and persistent cache).
     */
    public function forget(string $domain): void
    {
        $prefix = (string) ($this->config['token_cache']['key_prefix'] ?? 'bog-sdk:token:');
        $this->cache->forget($prefix.$domain);
        unset($this->runtimeCache[$domain]);
    }

    /**
     * Fetch a fresh OAuth2 token from the token endpoint for the given domain.
     *
     * @throws BogAuthenticationException
     */
    private function fetch(string $domain): AccessToken
    {
        $domainConfig = $this->config[$domain] ?? [];
        $tokenUrl = (string) ($domainConfig['token_url'] ?? '');
        $clientId = (string) ($domainConfig['client_id'] ?? '');
        $clientSecret = (string) ($domainConfig['client_secret'] ?? '');

        if ($tokenUrl === '' || $clientId === '' || $clientSecret === '') {
            throw new BogAuthenticationException("Missing OAuth credentials for domain '{$domain}'. Check config/bog-sdk.php.");
        }

        $response = $this->http
            ->withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post($tokenUrl, ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            throw new BogAuthenticationException(
                "OAuth token request failed for '{$domain}': HTTP {$response->status()} — {$response->body()}"
            );
        }

        $data = $response->json();
        if (! is_array($data) || empty($data['access_token'])) {
            throw new BogAuthenticationException(
                "OAuth token response for '{$domain}' did not contain access_token."
            );
        }

        return AccessToken::fromArray($data);
    }
}
