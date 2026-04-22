<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments\Enums;

enum CaptureMethod: string
{
    case AUTOMATIC = 'automatic';
    case MANUAL = 'manual';
}
