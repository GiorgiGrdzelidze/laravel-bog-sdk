<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Exceptions;

/**
 * Thrown when a BOG payment is declined by the bank or card issuer.
 */
final class BogPaymentDeclinedException extends BogPaymentException {}
