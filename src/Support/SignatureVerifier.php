<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Support;

use GiorgiGrdzelidze\BogSdk\Exceptions\BogConfigurationException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogInvalidSignatureException;

/**
 * Verifies BOG callback signatures using RSA-SHA256 with the BOG public key.
 */
final class SignatureVerifier
{
    public function __construct(
        private readonly string $publicKeyPath,
    ) {}

    /**
     * Verify a callback signature against the raw request body.
     *
     * @param  string  $rawBody  The raw HTTP request body.
     * @param  string  $signatureBase64  The base64-encoded RSA-SHA256 signature.
     */
    public function verify(string $rawBody, string $signatureBase64): bool
    {
        $publicKey = $this->loadPublicKey();
        $signature = base64_decode($signatureBase64, true);

        if ($signature === false) {
            return false;
        }

        $result = openssl_verify($rawBody, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        return $result === 1;
    }

    /**
     * Verify a callback signature, throwing on failure.
     *
     * @throws BogInvalidSignatureException
     */
    public function verifyOrFail(string $rawBody, string $signatureBase64): void
    {
        if (! $this->verify($rawBody, $signatureBase64)) {
            throw new BogInvalidSignatureException('BOG callback signature verification failed.');
        }
    }

    /**
     * Load the BOG public key from the configured PEM file.
     *
     * @throws BogConfigurationException
     */
    private function loadPublicKey(): \OpenSSLAsymmetricKey
    {
        if (! file_exists($this->publicKeyPath)) {
            throw new BogConfigurationException(
                "BOG payments callback public key not found at: {$this->publicKeyPath}. "
                .'Run: php artisan vendor:publish --tag=bog-sdk-keys'
            );
        }

        $pem = file_get_contents($this->publicKeyPath);
        if ($pem === false) {
            throw new BogConfigurationException("Could not read public key at: {$this->publicKeyPath}");
        }

        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            throw new BogConfigurationException("Invalid public key at: {$this->publicKeyPath}");
        }

        return $key;
    }
}
