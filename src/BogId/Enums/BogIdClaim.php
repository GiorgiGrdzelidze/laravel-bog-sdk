<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\BogId\Enums;

enum BogIdClaim: string
{
    case FPI = 'FPI';
    case DI = 'DI';
    case BI = 'BI';
    case CI = 'CI';
    case BPI = 'BPI';
    case PI = 'PI';

    public function description(): string
    {
        return match ($this) {
            self::FPI => 'Full personal info',
            self::DI => 'Document info',
            self::BI => 'Bank info',
            self::CI => 'Contact info',
            self::BPI => 'Basic personal info',
            self::PI => 'Personal info',
        };
    }
}
