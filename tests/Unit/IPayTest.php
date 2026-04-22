<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\BogClient;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayItemDto;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayOrderRequestDto;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayOrderResponseDto;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayPaymentDetailsDto;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

final class IPayTest extends TestCase
{
    private function fakeIPayToken(): void
    {
        Http::fake([
            'ipay-test.bog.ge/opay/api/v1/oauth2/token' => Http::response([
                'access_token' => 'ipay-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);
    }

    public function test_create_ipay_order(): void
    {
        Http::fake([
            '*oauth*' => Http::response([
                'access_token' => 'ipay-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'ipay-test.bog.ge/opay/api/v1/checkout/orders' => Http::response([
                'order_id' => 'ipay-order-123',
                'redirect_url' => 'https://ipay.ge/pay/123',
                'status' => 'created',
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $request = new IPayOrderRequestDto(
            intent: 'CAPTURE',
            amount: 25.00,
            currency: 'GEL',
            items: [new IPayItemDto('sku-1', 'Test item', 1, 25.00)],
            callbackUrl: 'https://merchant.example/ipay/callback',
        );

        $response = $client->ipay()->orders()->create($request);

        $this->assertInstanceOf(IPayOrderResponseDto::class, $response);
        $this->assertSame('ipay-order-123', $response->orderId);
        $this->assertSame('https://ipay.ge/pay/123', $response->redirectUrl);
    }

    public function test_get_ipay_payment_details(): void
    {
        Http::fake([
            '*oauth*' => Http::response([
                'access_token' => 'ipay-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'ipay-test.bog.ge/opay/api/v1/checkout/payment/*' => Http::response([
                'order_id' => 'ipay-order-123',
                'status' => 'completed',
                'amount' => 25.00,
                'currency' => 'GEL',
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $details = $client->ipay()->orders()->get('ipay-order-123');

        $this->assertInstanceOf(IPayPaymentDetailsDto::class, $details);
        $this->assertSame('completed', $details->status);
        $this->assertSame(25.00, $details->amount);
    }

    public function test_ipay_refund(): void
    {
        Http::fake([
            '*oauth*' => Http::response([
                'access_token' => 'ipay-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'ipay-test.bog.ge/opay/api/v1/checkout/refund/*' => Http::response([
                'status' => 'refunded',
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->ipay()->orders()->refund('ipay-order-123');

        $this->assertSame('refunded', $result['status']);
    }
}
