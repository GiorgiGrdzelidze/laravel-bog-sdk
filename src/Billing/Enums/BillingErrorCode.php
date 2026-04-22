<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Billing\Enums;

enum BillingErrorCode: string
{
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case AUTH_ERROR = 'AUTH_ERROR';
    case NOT_FOUND = 'NOT_FOUND';
    case CONFLICT = 'CONFLICT';
    case RATE_LIMITED = 'RATE_LIMITED';
    case INTERNAL_ERROR = 'INTERNAL_ERROR';
    case PAYMENT_DECLINED = 'PAYMENT_DECLINED';
    case INSUFFICIENT_FUNDS = 'INSUFFICIENT_FUNDS';
    case CARD_EXPIRED = 'CARD_EXPIRED';
    case INVALID_CARD = 'INVALID_CARD';

    public function description(): string
    {
        return match ($this) {
            self::VALIDATION_ERROR => 'Request validation failed',
            self::AUTH_ERROR => 'Authentication error',
            self::NOT_FOUND => 'Resource not found',
            self::CONFLICT => 'Conflict with existing resource',
            self::RATE_LIMITED => 'Too many requests',
            self::INTERNAL_ERROR => 'Internal server error',
            self::PAYMENT_DECLINED => 'Payment was declined',
            self::INSUFFICIENT_FUNDS => 'Insufficient funds on card',
            self::CARD_EXPIRED => 'Card has expired',
            self::INVALID_CARD => 'Invalid card number or details',
        };
    }
}
