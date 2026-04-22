<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Exceptions;

/**
 * Thrown when a BOG Billing API operation returns an error.
 */
final class BogBillingException extends BogSdkException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        /** @var array<string, mixed> */
        public readonly array $details = [],
    ) {
        parent::__construct("Billing error [{$errorCode}]: {$message}");
    }
}
