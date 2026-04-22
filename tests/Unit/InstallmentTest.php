<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\BogClient;
use GiorgiGrdzelidze\BogSdk\Installment\Dto\CalculatorRequestDto;
use GiorgiGrdzelidze\BogSdk\Installment\Dto\DiscountDto;
use GiorgiGrdzelidze\BogSdk\Installment\Dto\InstallmentBasketItemDto;
use GiorgiGrdzelidze\BogSdk\Support\InstallmentRules;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

final class InstallmentTest extends TestCase
{
    public function test_calculator_discounts(): void
    {
        Http::fake([
            '*oauth*' => Http::response([
                'access_token' => 'installment-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.bog.ge/installment/v1/services/installment/calculator' => Http::response([
                'discounts' => [
                    ['code' => 'DISC01', 'description' => '3 months 0%', 'amount' => 0, 'month' => 3],
                    ['code' => 'DISC02', 'description' => '6 months 5%', 'amount' => 5.00, 'month' => 6],
                ],
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $request = new CalculatorRequestDto(
            clientType: 'standard',
            invoiceCurrency: 'GEL',
            basket: [new InstallmentBasketItemDto('prod-1', 200.00, 1)],
            totalItemAmount: 200.00,
            totalAmount: 200.00,
        );

        $discounts = $client->installment()->calculator()->discounts($request);

        $this->assertCount(2, $discounts);
        $this->assertInstanceOf(DiscountDto::class, $discounts[0]);
        $this->assertSame('DISC01', $discounts[0]->code);
        $this->assertSame(3, $discounts[0]->month);
        $this->assertSame(6, $discounts[1]->month);
    }

    public function test_js_config(): void
    {
        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);

        $basket = [
            ['product_id' => 'p1', 'total_item_amount' => 100.00, 'total_item_qty' => 1],
            ['product_id' => 'p2', 'total_item_amount' => 50.00, 'total_item_qty' => 2],
        ];

        $config = $client->installment()->jsConfig($basket, 'https://example.com/complete');

        $this->assertSame('test-shop-id', $config['shop_id']);
        $this->assertSame($basket, $config['basket']);
        $this->assertSame('GEL', $config['currency']);
        $this->assertSame(150.00, $config['amount']);
        $this->assertSame('https://example.com/complete', $config['on_complete_url']);
    }

    public function test_installment_rules_constants(): void
    {
        $this->assertSame(100.00, InstallmentRules::MIN_AMOUNT);
        $this->assertSame(10_000.00, InstallmentRules::MAX_AMOUNT);
        $this->assertSame('GEL', InstallmentRules::CURRENCY);
        $this->assertSame([3, 6, 9, 12, 18, 24], InstallmentRules::ALLOWED_MONTHS);
    }

    public function test_installment_order_details(): void
    {
        Http::fake([
            '*oauth*' => Http::response([
                'access_token' => 'installment-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
            'api-test.bog.ge/installment/v1/services/installment/details/*' => Http::response([
                'order_id' => 'inst-order-123',
                'status' => 'approved',
            ]),
        ]);

        /** @var BogClient $client */
        $client = $this->app->make(BogClient::class);
        $details = $client->installment()->checkout()->details('inst-order-123');

        $this->assertSame('inst-order-123', $details->orderId);
        $this->assertSame('approved', $details->status);
    }
}
