<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Enums;

enum PaymentMethod: string
{
    case CARD = 'card';
    case BOG_P2P = 'bog_p2p';
    case BOG_LOYALTY = 'bog_loyalty';
    case BNPL = 'bnpl';
    case APPLE_PAY = 'apple_pay';
    case GOOGLE_PAY = 'google_pay';
}
