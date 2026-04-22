<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentRequestDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentResponseDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentStatusDto;
use GiorgiGrdzelidze\BogSdk\Billing\Enums\BillingErrorCode;
use GiorgiGrdzelidze\BogSdk\BogClient;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogBillingException;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

final class BillingTest extends TestCase
{
    public function test_billing_payment(): void
    {
        Http::fake([
            '*oauth*' => Http::response([
                'access_token' => 'billing-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.bog.ge/billing/v1/payment' => Http::response([
                'payment_id' => 'pay-123',
                'status' => 'pending',
                'redirect_url' => 'https://payment.bog.ge/billing/123',
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $request = new PaymentRequestDto(100.00, 'GEL', 'Test payment');
        $response = $client->billing()->payment($request);

        $this->assertInstanceOf(PaymentResponseDto::class, $response);
        $this->assertSame('pay-123', $response->paymentId);
        $this->assertSame('pending', $response->status);
    }

    public function test_billing_payment_status(): void
    {
        Http::fake([
            '*oauth*' => Http::response([
                'access_token' => 'billing-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.bog.ge/billing/v1/payment/*' => Http::response([
                'payment_id' => 'pay-123',
                'status' => 'completed',
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $status = $client->billing()->paymentStatus('pay-123');

        $this->assertInstanceOf(PaymentStatusDto::class, $status);
        $this->assertSame('completed', $status->status);
    }

    public function test_billing_throws_on_error_response(): void
    {
        Http::fake([
            '*oauth*' => Http::response([
                'access_token' => 'billing-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.bog.ge/billing/v1/payment' => Http::response([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Amount is required',
                    'details' => ['field' => 'amount'],
                ],
            ], 400),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);

        $this->expectException(BogBillingException::class);
        $this->expectExceptionMessage('VALIDATION_ERROR');

        $client->billing()->payment(new PaymentRequestDto(0, 'GEL', 'Test'));
    }

    public function test_billing_error_code_enum(): void
    {
        $this->assertSame('VALIDATION_ERROR', BillingErrorCode::VALIDATION_ERROR->value);
        $this->assertSame('Request validation failed', BillingErrorCode::VALIDATION_ERROR->description());
        $this->assertSame('Payment was declined', BillingErrorCode::PAYMENT_DECLINED->description());
    }
}
