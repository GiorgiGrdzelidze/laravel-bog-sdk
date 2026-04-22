<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Contracts;

/**
 * Contract for the token-aware HTTP client used by all SDK endpoints.
 */
interface HttpClientContract
{
    /**
     * Send an authenticated GET request and return the JSON-decoded response.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $domain, string $url, array $query = []): array;

    /**
     * Send an authenticated POST request and return the JSON-decoded response.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function post(string $domain, string $url, array $body = []): array;

    /**
     * Send an authenticated PUT request and return the JSON-decoded response.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function put(string $domain, string $url, array $body = []): array;

    /**
     * Send an authenticated PATCH request and return the JSON-decoded response.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function patch(string $domain, string $url, array $body = []): array;

    /**
     * Send an authenticated DELETE request and return the JSON-decoded response.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function delete(string $domain, string $url, array $body = []): array;

    /**
     * Send an authenticated GET request and return the raw response body as a string.
     *
     * Useful for endpoints that return plain text or scalar values instead of JSON.
     *
     * @param  array<string, mixed>  $query
     */
    public function getRaw(string $domain, string $url, array $query = []): string;
}
