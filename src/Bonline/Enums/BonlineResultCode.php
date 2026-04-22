<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Enums;

enum BonlineResultCode: string
{
    case SUCCESS = '0';
    case INVALID_CREDENTIALS = '0001';
    case ACCOUNT_NOT_FOUND = '0002';
    case INVALID_PERIOD = '0003';
    case PAGING_CURSOR_EXPIRED = '0004';

    public function description(): string
    {
        return match ($this) {
            self::SUCCESS => 'Success',
            self::INVALID_CREDENTIALS => 'Invalid credentials',
            self::ACCOUNT_NOT_FOUND => 'Account not found',
            self::INVALID_PERIOD => 'Invalid period',
            self::PAGING_CURSOR_EXPIRED => 'Paging cursor expired',
        };
    }
}
