<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Support;

/**
 * Constants defining BOG installment business rules and constraints.
 */
final class InstallmentRules
{
    public const float MIN_AMOUNT = 100.00;

    public const float MAX_AMOUNT = 10_000.00;

    public const string CURRENCY = 'GEL';

    /** @var int[] */
    public const array ALLOWED_MONTHS = [3, 6, 9, 12, 18, 24];
}
