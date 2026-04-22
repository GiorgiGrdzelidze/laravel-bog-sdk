<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\BogId;

use GiorgiGrdzelidze\BogSdk\BogId\Dto\BogIdTokenDto;
use GiorgiGrdzelidze\BogSdk\BogId\Dto\BogIdUserDto;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogIdException;
use Illuminate\Http\Client\Factory;

/**
 * BOG-ID (OpenID Connect) client for user authentication and identity.
 */
final class BogIdClient
{
    /**
     * @param  array<string, mixed>  $config  BOG-ID configuration (issuer, client_id, client_secret).
     */
    public function __construct(
        private readonly Factory $http,
        private readonly array $config,
    ) {}

    /**
     * @param  string[]  $scopes
     */
    /**
     * Build the OpenID Connect authorization redirect URL.
     */
    public function redirectUrl(array $scopes, string $redirectUri, string $state): string
    {
        $issuer = rtrim((string) ($this->config['issuer'] ?? ''), '/');
        $clientId = (string) ($this->config['client_id'] ?? '');

        $query = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'scope' => 'openid '.implode(' ', $scopes),
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);

        return $issuer.'/protocol/openid-connect/auth?'.$query;
    }

    /**
     * Exchange an authorization code for access/refresh tokens.
     *
     * @throws BogIdException
     */
    public function exchangeCode(string $code, string $redirectUri): BogIdTokenDto
    {
        $issuer = rtrim((string) ($this->config['issuer'] ?? ''), '/');
        $tokenUrl = $issuer.'/protocol/openid-connect/token';

        $response = $this->http
            ->asForm()
            ->post($tokenUrl, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => (string) ($this->config['client_id'] ?? ''),
                'client_secret' => (string) ($this->config['client_secret'] ?? ''),
            ]);

        if ($response->failed()) {
            throw new BogIdException('BOG-ID token exchange failed: HTTP '.$response->status().' — '.$response->body());
        }

        $data = $response->json();
        if (! is_array($data) || empty($data['access_token'])) {
            throw new BogIdException('BOG-ID token exchange did not return access_token.');
        }

        return BogIdTokenDto::fromArray($data);
    }

    /**
     * Fetch the authenticated user's profile from the userinfo endpoint.
     *
     * @throws BogIdException
     */
    public function userinfo(string $accessToken): BogIdUserDto
    {
        $issuer = rtrim((string) ($this->config['issuer'] ?? ''), '/');
        $userinfoUrl = $issuer.'/protocol/openid-connect/userinfo';

        $response = $this->http
            ->withToken($accessToken)
            ->acceptJson()
            ->get($userinfoUrl);

        if ($response->failed()) {
            throw new BogIdException('BOG-ID userinfo request failed: HTTP '.$response->status().' — '.$response->body());
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new BogIdException('BOG-ID userinfo response is not valid JSON.');
        }

        return BogIdUserDto::fromArray($data);
    }
}
