<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\BogId\Dto;

/**
 * Token DTO returned after a BOG-ID authorization code exchange.
 */
final readonly class BogIdTokenDto
{
    public function __construct(
        public string $accessToken,
        public string $idToken,
        public int $expiresIn,
        public string $refreshToken,
        public string $tokenType,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: (string) ($data['access_token'] ?? ''),
            idToken: (string) ($data['id_token'] ?? ''),
            expiresIn: (int) ($data['expires_in'] ?? 0),
            refreshToken: (string) ($data['refresh_token'] ?? ''),
            tokenType: (string) ($data['token_type'] ?? 'Bearer'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'id_token' => $this->idToken,
            'expires_in' => $this->expiresIn,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
        ];
    }
}
