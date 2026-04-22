<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Enums;

enum BogPaymentResponseCode: int
{
    case SUCCESS = 100;
    case BLOCKED = 101;
    case COMPLETED_AFTER_BLOCK = 102;
    case PARTIAL_COMPLETED_AFTER_BLOCK = 103;
    case REFUNDED = 104;
    case REFUNDED_PARTIALLY = 105;
    case REJECTED_BY_ACQUIRER = 106;
    case REJECTED_BY_FRAUD = 107;
    case REJECTED_BY_LIMIT = 108;
    case REJECTED_BY_TIMEOUT = 109;
    case REJECTED_BY_USER = 110;
    case REJECTED_CARD_EXPIRED = 111;
    case REJECTED_INSUFFICIENT_FUNDS = 112;
    case REJECTED_INVALID_CARD = 113;
    case REJECTED_3DS_FAILED = 114;
    case REJECTED_CARD_RESTRICTED = 115;
    case REJECTED_BY_ISSUER = 116;
    case CANCELLED = 117;

    case VALIDATION_ERROR = 200;
    case INVALID_AMOUNT = 201;
    case INVALID_CURRENCY = 202;
    case INVALID_ORDER = 203;
    case INVALID_CALLBACK_URL = 204;
    case INVALID_REDIRECT_URL = 205;
    case INVALID_PAYMENT_METHOD = 206;
    case INVALID_TTL = 207;
    case INVALID_BASKET = 208;
    case INVALID_BUYER = 209;
    case INVALID_EXTERNAL_ORDER_ID = 210;
    case DUPLICATE_EXTERNAL_ORDER_ID = 211;

    case AUTH_ERROR = 300;

    case NOT_FOUND = 400;

    case INTERNAL_ERROR = 500;

    case RATE_LIMITED = 600;

    public function description(): string
    {
        return match ($this) {
            self::SUCCESS => 'Payment completed successfully',
            self::BLOCKED => 'Amount blocked (pre-auth)',
            self::COMPLETED_AFTER_BLOCK => 'Completed after block',
            self::PARTIAL_COMPLETED_AFTER_BLOCK => 'Partially completed after block',
            self::REFUNDED => 'Payment refunded',
            self::REFUNDED_PARTIALLY => 'Payment partially refunded',
            self::REJECTED_BY_ACQUIRER => 'Rejected by acquirer',
            self::REJECTED_BY_FRAUD => 'Rejected by fraud detection',
            self::REJECTED_BY_LIMIT => 'Rejected by limit',
            self::REJECTED_BY_TIMEOUT => 'Rejected by timeout',
            self::REJECTED_BY_USER => 'Rejected by user',
            self::REJECTED_CARD_EXPIRED => 'Card expired',
            self::REJECTED_INSUFFICIENT_FUNDS => 'Insufficient funds',
            self::REJECTED_INVALID_CARD => 'Invalid card',
            self::REJECTED_3DS_FAILED => '3DS authentication failed',
            self::REJECTED_CARD_RESTRICTED => 'Card restricted',
            self::REJECTED_BY_ISSUER => 'Rejected by issuer',
            self::CANCELLED => 'Payment cancelled',
            self::VALIDATION_ERROR => 'Validation error',
            self::INVALID_AMOUNT => 'Invalid amount',
            self::INVALID_CURRENCY => 'Invalid currency',
            self::INVALID_ORDER => 'Invalid order',
            self::INVALID_CALLBACK_URL => 'Invalid callback URL',
            self::INVALID_REDIRECT_URL => 'Invalid redirect URL',
            self::INVALID_PAYMENT_METHOD => 'Invalid payment method',
            self::INVALID_TTL => 'Invalid TTL',
            self::INVALID_BASKET => 'Invalid basket',
            self::INVALID_BUYER => 'Invalid buyer',
            self::INVALID_EXTERNAL_ORDER_ID => 'Invalid external order ID',
            self::DUPLICATE_EXTERNAL_ORDER_ID => 'Duplicate external order ID',
            self::AUTH_ERROR => 'Authentication error',
            self::NOT_FOUND => 'Not found',
            self::INTERNAL_ERROR => 'Internal server error',
            self::RATE_LIMITED => 'Rate limited',
        };
    }
}
