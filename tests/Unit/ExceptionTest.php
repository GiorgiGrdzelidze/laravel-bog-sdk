<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\Exceptions\BogAuthenticationException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogBillingException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogBonlineException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogConfigurationException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogHttpException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogIdException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogInstallmentException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogInvalidSignatureException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogIPayException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogOpenBankingException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogPaymentDeclinedException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogPaymentException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogPaymentValidationException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogSdkException;
use PHPUnit\Framework\TestCase;

final class ExceptionTest extends TestCase
{
    public function test_all_exceptions_extend_bog_sdk_exception(): void
    {
        $exceptions = [
            new BogConfigurationException('test'),
            new BogHttpException(500, 'body', '/url', 'ERR'),
            new BogAuthenticationException('test'),
            new BogInvalidSignatureException('test'),
            new BogBonlineException('0001', 'Invalid credentials'),
            new BogPaymentException('test', '100', 'Success'),
            new BogPaymentDeclinedException('test'),
            new BogPaymentValidationException('test'),
            new BogBillingException('VALIDATION_ERROR', 'Bad request'),
            new BogIPayException('test'),
            new BogInstallmentException('test'),
            new BogIdException('test'),
            new BogOpenBankingException('test'),
        ];

        foreach ($exceptions as $e) {
            $this->assertInstanceOf(BogSdkException::class, $e);
        }
    }

    public function test_http_exception_carries_metadata(): void
    {
        $e = new BogHttpException(
            status: 422,
            body: '{"error":"validation"}',
            url: '/api/test',
            bogErrorCode: 'ERR-001',
        );

        $this->assertSame(422, $e->status);
        $this->assertSame('{"error":"validation"}', $e->body);
        $this->assertSame('/api/test', $e->url);
        $this->assertSame('ERR-001', $e->bogErrorCode);
        $this->assertStringContainsString('422', $e->getMessage());
        $this->assertStringContainsString('/api/test', $e->getMessage());
        $this->assertStringContainsString('ERR-001', $e->getMessage());
    }

    public function test_bonline_exception_message(): void
    {
        $e = new BogBonlineException('0002', 'Account not found');
        $this->assertSame('0002', $e->resultCode);
        $this->assertStringContainsString('0002', $e->getMessage());
        $this->assertStringContainsString('Account not found', $e->getMessage());
    }

    public function test_billing_exception_carries_details(): void
    {
        $e = new BogBillingException('VALIDATION_ERROR', 'Bad request', ['field' => 'amount']);
        $this->assertSame('VALIDATION_ERROR', $e->errorCode);
        $this->assertSame(['field' => 'amount'], $e->details);
    }

    public function test_payment_exception_hierarchy(): void
    {
        $declined = new BogPaymentDeclinedException('Declined', '106');
        $validation = new BogPaymentValidationException('Invalid', '200');

        $this->assertInstanceOf(BogPaymentException::class, $declined);
        $this->assertInstanceOf(BogPaymentException::class, $validation);
        $this->assertSame('106', $declined->responseCode);
    }
}
