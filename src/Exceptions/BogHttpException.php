<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Exceptions;

use Illuminate\Http\Client\Response;
use Throwable;

/**
 * Thrown when a BOG API request returns an HTTP error response.
 */
final class BogHttpException extends BogSdkException
{
    public function __construct(
        public readonly int $status,
        public readonly ?string $body = null,
        public readonly ?string $url = null,
        public readonly ?string $bogErrorCode = null,
        ?Throwable $previous = null,
    ) {
        $message = "BOG HTTP error {$status}";
        if ($url !== null) {
            $message .= " for {$url}";
        }
        if ($bogErrorCode !== null) {
            $message .= " (code: {$bogErrorCode})";
        }

        parent::__construct($message, $status, $previous);
    }

    /**
     * Create an exception from a failed HTTP response.
     */
    public static function fromResponse(Response $response, string $url): self
    {
        $body = $response->body();
        $json = $response->json();
        $bogCode = is_array($json) ? ($json['error']['code'] ?? $json['ResultCode'] ?? null) : null;

        return new self(
            status: $response->status(),
            body: $body,
            url: $url,
            bogErrorCode: $bogCode !== null ? (string) $bogCode : null,
        );
    }
}
