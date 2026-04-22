<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Exceptions;

/**
 * Thrown when BOG OAuth2 authentication fails (token acquisition or refresh).
 */
final class BogAuthenticationException extends BogSdkException {}
