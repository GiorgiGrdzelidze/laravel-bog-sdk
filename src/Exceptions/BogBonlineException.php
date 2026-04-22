<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Exceptions;

/**
 * Thrown when the Bonline API returns a business-level error (non-zero ResultCode).
 */
final class BogBonlineException extends BogSdkException
{
    public function __construct(
        public readonly string $resultCode,
        public readonly string $resultDescription,
    ) {
        parent::__construct("Bonline error [{$resultCode}]: {$resultDescription}");
    }
}
