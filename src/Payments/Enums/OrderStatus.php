<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Enums;

enum OrderStatus: string
{
    case CREATED = 'created';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case REFUNDED = 'refunded';
    case REFUNDED_PARTIALLY = 'refunded_partially';
    case AUTH_REQUESTED = 'auth_requested';
    case BLOCKED = 'blocked';
    case PARTIAL_COMPLETED = 'partial_completed';
}
