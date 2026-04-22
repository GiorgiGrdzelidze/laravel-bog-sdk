<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Auth\Dto;

/**
 * OAuth2 access token response DTO.
 */
final readonly class AccessToken
{
    public function __construct(
        public string $accessToken,
        public int $expiresIn,
        public string $tokenType,
    ) {}

    /**
     * Create an AccessToken from the raw OAuth2 JSON response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: (string) ($data['access_token'] ?? ''),
            expiresIn: (int) ($data['expires_in'] ?? 0),
            tokenType: (string) ($data['token_type'] ?? 'Bearer'),
        );
    }

    /**
     * Convert the DTO back to the OAuth2 response format.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'expires_in' => $this->expiresIn,
            'token_type' => $this->tokenType,
        ];
    }
}
