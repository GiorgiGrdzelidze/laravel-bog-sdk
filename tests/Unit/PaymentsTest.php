<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\BogClient;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\BasketItemDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\BuyerDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\CreateOrderRequestDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\CreateOrderResponseDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\OrderDetailsDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\SplitAccountDto;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

final class PaymentsTest extends TestCase
{
    private function fakeTokenAndEndpoint(string $urlPattern, array $response): void
    {
        Http::fake([
            'oauth2-test.bog.ge/*' => Http::response([
                'access_token' => 'payments-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            $urlPattern => Http::response($response),
        ]);
    }

    public function test_create_order(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/ecommerce/orders',
            [
                'id' => 'order-uuid-123',
                '_links' => [
                    'redirect' => ['href' => 'https://payment.bog.ge/redirect/123'],
                    'details' => ['href' => 'https://api.bog.ge/payments/v1/receipt/order-uuid-123'],
                ],
            ],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $request = new CreateOrderRequestDto(
            callbackUrl: 'https://merchant.example/callback',
            externalOrderId: 'MERCHANT-001',
            currency: 'GEL',
            totalAmount: 12.34,
            basket: [
                new BasketItemDto('sku-1', 'Test product', 1, 12.34),
            ],
            successUrl: 'https://merchant.example/success',
            failUrl: 'https://merchant.example/fail',
            buyer: new BuyerDto('Test User', 'test@example.com', '+995599000000'),
        );

        $response = $client->payments()->orders()->create($request);

        $this->assertInstanceOf(CreateOrderResponseDto::class, $response);
        $this->assertSame('order-uuid-123', $response->id);
        $this->assertSame('https://payment.bog.ge/redirect/123', $response->redirectUrl);
    }

    public function test_get_order_details(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/receipt/*',
            [
                'id' => 'order-uuid-123',
                'order_status' => ['key' => 'completed'],
                'external_order_id' => 'MERCHANT-001',
                'purchase_units' => ['total_amount' => 12.34, 'currency' => 'GEL'],
                'payment_detail' => ['payment_method' => 'card', 'card_mask' => '4***1234', 'rrn' => '123456789'],
            ],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $details = $client->payments()->orders()->get('order-uuid-123');

        $this->assertInstanceOf(OrderDetailsDto::class, $details);
        $this->assertSame('order-uuid-123', $details->id);
        $this->assertSame('completed', $details->statusKey);
        $this->assertSame(12.34, $details->totalAmount);
        $this->assertSame('4***1234', $details->cardMask);
    }

    public function test_refund_order(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/services/orders/refund/*',
            ['status' => 'refunded'],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->payments()->orders()->refund('order-uuid-123', 5.00);

        $this->assertSame('refunded', $result['status']);
    }

    public function test_cancel_order(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/services/orders/*/cancel',
            ['status' => 'rejected'],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->payments()->orders()->cancel('order-uuid-123');

        $this->assertSame('rejected', $result['status']);
    }

    public function test_confirm_preauth(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/services/orders/*/confirm',
            ['status' => 'completed'],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->payments()->orders()->confirm('order-uuid-123', 10.00);

        $this->assertSame('completed', $result['status']);
    }

    public function test_charge_saved_card(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/charges/card/*',
            ['status' => 'completed', 'id' => 'charge-uuid'],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->payments()->cardCharges()->charge('parent-order-id', 50.00);

        $this->assertSame('completed', $result['status']);
    }

    public function test_delete_saved_card(): void
    {
        Http::fake([
            'oauth2-test.bog.ge/*' => Http::response([
                'access_token' => 'payments-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.bog.ge/payments/v1/charges/card/*' => Http::response(['status' => 'deleted']),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->payments()->cardCharges()->deleteCard('parent-order-id');

        $this->assertSame('deleted', $result['status']);
    }

    public function test_google_pay_complete(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/googlepay/complete',
            ['status' => 'completed', 'order_id' => 'gp-order-123'],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->payments()->googlePay()->complete([
            'token' => 'google-pay-token-data',
            'order_id' => 'merchant-order-001',
        ]);

        $this->assertSame('completed', $result['status']);
        $this->assertSame('gp-order-123', $result['order_id']);
    }

    public function test_apple_pay_complete(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/applepay/complete',
            ['status' => 'completed', 'order_id' => 'ap-order-123'],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->payments()->applePay()->complete([
            'token' => 'apple-pay-token-data',
        ]);

        $this->assertSame('completed', $result['status']);
    }

    public function test_split_payment(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/services/split-payment/create',
            ['status' => 'created'],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->payments()->splitPayment()->create('order-123', [
            new SplitAccountDto('GE00ACC1', 50.00),
            new SplitAccountDto('GE00ACC2', 50.00),
        ]);

        $this->assertSame('created', $result['status']);
    }

    public function test_subscription_charge(): void
    {
        $this->fakeTokenAndEndpoint(
            'api-test.bog.ge/payments/v1/charges/card/subscription/*',
            ['status' => 'completed', 'id' => 'sub-charge-uuid'],
        );

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $result = $client->payments()->cardCharges()->subscription('parent-order-id', 30.00, 'GEL', 'EXT-SUB-001');

        $this->assertSame('completed', $result['status']);
    }

    public function test_create_order_request_dto_to_array(): void
    {
        $dto = new CreateOrderRequestDto(
            callbackUrl: 'https://example.com/callback',
            externalOrderId: 'EXT-001',
            currency: 'GEL',
            totalAmount: 99.99,
            basket: [new BasketItemDto('p1', 'Product 1', 2, 49.995)],
            successUrl: 'https://example.com/success',
            capture: 'manual',
            saveCard: true,
            saveCardToDate: '12/28',
        );

        $array = $dto->toArray();

        $this->assertSame('https://example.com/callback', $array['callback_url']);
        $this->assertSame('EXT-001', $array['external_order_id']);
        $this->assertSame('manual', $array['capture']);
        $this->assertTrue($array['save_card']);
        $this->assertSame('12/28', $array['save_card_to_date']);
        $this->assertSame(99.99, $array['purchase_units']['total_amount']);
        $this->assertSame('GEL', $array['purchase_units']['currency']);
        $this->assertCount(1, $array['purchase_units']['basket']);
    }
}
