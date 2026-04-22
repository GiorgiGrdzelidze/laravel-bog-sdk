<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Exceptions;

/**
 * Thrown when a BOG payment operation fails.
 */
class BogPaymentException extends BogSdkException
{
    public function __construct(
        string $message,
        public readonly ?string $responseCode = null,
        public readonly ?string $responseDescription = null,
    ) {
        parent::__construct($message);
    }
}
