<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\Exceptions\BogConfigurationException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogInvalidSignatureException;
use GiorgiGrdzelidze\BogSdk\Support\SignatureVerifier;
use PHPUnit\Framework\TestCase;

final class SignatureVerifierTest extends TestCase
{
    private string $privateKeyPath;

    private string $publicKeyPath;

    protected function setUp(): void
    {
        parent::setUp();

        $tempDir = sys_get_temp_dir().'/bog-sdk-test-'.uniqid();
        mkdir($tempDir, 0755, true);

        $this->privateKeyPath = $tempDir.'/test-private.pem';
        $this->publicKeyPath = $tempDir.'/test-public.pem';

        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($key, $privateKeyPem);
        $details = openssl_pkey_get_details($key);

        file_put_contents($this->privateKeyPath, $privateKeyPem);
        file_put_contents($this->publicKeyPath, $details['key']);
    }

    protected function tearDown(): void
    {
        @unlink($this->privateKeyPath);
        @unlink($this->publicKeyPath);
        @rmdir(dirname($this->privateKeyPath));
        parent::tearDown();
    }

    public function test_verify_valid_signature(): void
    {
        $body = '{"order_id":"123","status":"completed"}';

        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));
        openssl_sign($body, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureBase64 = base64_encode($signature);

        $verifier = new SignatureVerifier($this->publicKeyPath);
        $this->assertTrue($verifier->verify($body, $signatureBase64));
    }

    public function test_verify_invalid_signature(): void
    {
        $verifier = new SignatureVerifier($this->publicKeyPath);
        $this->assertFalse($verifier->verify('some body', 'invalid-base64-signature'));
    }

    public function test_verify_tampered_body(): void
    {
        $body = '{"order_id":"123"}';
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privateKeyPath));
        openssl_sign($body, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureBase64 = base64_encode($signature);

        $verifier = new SignatureVerifier($this->publicKeyPath);
        $this->assertFalse($verifier->verify('{"order_id":"TAMPERED"}', $signatureBase64));
    }

    public function test_verify_or_fail_throws_on_invalid(): void
    {
        $verifier = new SignatureVerifier($this->publicKeyPath);

        $this->expectException(BogInvalidSignatureException::class);
        $verifier->verifyOrFail('body', 'bad-sig');
    }

    public function test_throws_when_key_file_missing(): void
    {
        $verifier = new SignatureVerifier('/nonexistent/path/key.pem');

        $this->expectException(BogConfigurationException::class);
        $this->expectExceptionMessage('public key not found');

        $verifier->verify('body', base64_encode('sig'));
    }
}
