<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\Bonline\Enums\BonlineResultCode;
use GiorgiGrdzelidze\BogSdk\Payments\Enums\BogPaymentResponseCode;
use GiorgiGrdzelidze\BogSdk\Payments\Enums\CaptureMethod;
use GiorgiGrdzelidze\BogSdk\Payments\Enums\OrderStatus;
use GiorgiGrdzelidze\BogSdk\Payments\Enums\PaymentMethod;
use GiorgiGrdzelidze\BogSdk\Support\CurrencyCode;
use PHPUnit\Framework\TestCase;

final class EnumTest extends TestCase
{
    public function test_bonline_result_codes(): void
    {
        $this->assertSame('0', BonlineResultCode::SUCCESS->value);
        $this->assertSame('0001', BonlineResultCode::INVALID_CREDENTIALS->value);
        $this->assertSame('Success', BonlineResultCode::SUCCESS->description());
        $this->assertSame('Paging cursor expired', BonlineResultCode::PAGING_CURSOR_EXPIRED->description());
    }

    public function test_payment_response_codes(): void
    {
        $this->assertSame(100, BogPaymentResponseCode::SUCCESS->value);
        $this->assertSame('Payment completed successfully', BogPaymentResponseCode::SUCCESS->description());
        $this->assertSame('Insufficient funds', BogPaymentResponseCode::REJECTED_INSUFFICIENT_FUNDS->description());
        $this->assertSame('Rate limited', BogPaymentResponseCode::RATE_LIMITED->description());
    }

    public function test_order_status_values(): void
    {
        $this->assertSame('created', OrderStatus::CREATED->value);
        $this->assertSame('completed', OrderStatus::COMPLETED->value);
        $this->assertSame('blocked', OrderStatus::BLOCKED->value);
        $this->assertSame('refunded_partially', OrderStatus::REFUNDED_PARTIALLY->value);
    }

    public function test_payment_method_values(): void
    {
        $this->assertSame('card', PaymentMethod::CARD->value);
        $this->assertSame('apple_pay', PaymentMethod::APPLE_PAY->value);
        $this->assertSame('google_pay', PaymentMethod::GOOGLE_PAY->value);
        $this->assertSame('bnpl', PaymentMethod::BNPL->value);
    }

    public function test_capture_method_values(): void
    {
        $this->assertSame('automatic', CaptureMethod::AUTOMATIC->value);
        $this->assertSame('manual', CaptureMethod::MANUAL->value);
    }

    public function test_currency_code_values(): void
    {
        $this->assertSame('GEL', CurrencyCode::GEL->value);
        $this->assertSame('USD', CurrencyCode::USD->value);
        $this->assertSame('EUR', CurrencyCode::EUR->value);
    }
}
