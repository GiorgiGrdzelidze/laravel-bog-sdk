<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Support;

/**
 * Supported currency codes for BOG API operations.
 */
enum CurrencyCode: string
{
    case GEL = 'GEL';
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
}
