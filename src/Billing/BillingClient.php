<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Billing;

use GiorgiGrdzelidze\BogSdk\Auth\TokenManager;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\CancelResponseDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentInfoDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentRequestDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentResponseDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentStatusDto;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogBillingException;
use GiorgiGrdzelidze\BogSdk\Exceptions\BogHttpException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;

/**
 * BOG Billing API client.
 *
 * Supports four authentication methods: oauth2, basic, apikey, hmac-sha256.
 */
final class BillingClient
{
    /**
     * @param  array<string, mixed>  $config  Billing domain config (merged with http config).
     */
    public function __construct(
        private readonly Factory $http,
        private readonly TokenManager $tokens,
        private readonly array $config,
    ) {}

    /**
     * Create a new billing payment.
     */
    public function payment(PaymentRequestDto $request): PaymentResponseDto
    {
        $data = $this->request('post', '/payment', $request->toArray());

        return PaymentResponseDto::fromArray($data);
    }

    /**
     * Get the status of a billing payment.
     */
    public function paymentStatus(string $paymentId): PaymentStatusDto
    {
        $data = $this->request('get', '/payment/'.$paymentId);

        return PaymentStatusDto::fromArray($data);
    }

    /**
     * Cancel a billing payment.
     */
    public function cancelPayment(string $paymentId): CancelResponseDto
    {
        $data = $this->request('post', '/payment/'.$paymentId.'/cancel');

        return CancelResponseDto::fromArray($data);
    }

    /**
     * Send additional information for a billing payment.
     */
    public function sendPaymentInfo(PaymentInfoDto $dto): void
    {
        $this->request('post', '/payment/info', $dto->toArray());
    }

    /**
     * Execute an authenticated request against the Billing API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws BogBillingException
     * @throws BogHttpException
     */
    private function request(string $method, string $path, array $data = []): array
    {
        $baseUrl = rtrim((string) ($this->config['base_url'] ?? ''), '/');
        $url = $baseUrl.$path;

        $pending = $this->buildPendingRequest();
        $response = match ($method) {
            'get' => $pending->get($url, $data),
            'post' => $pending->post($url, $data),
            default => $pending->send($method, $url, ['json' => $data]),
        };

        if ($response->failed()) {
            $json = $response->json();
            if (is_array($json) && isset($json['error']['code'])) {
                throw new BogBillingException(
                    errorCode: (string) $json['error']['code'],
                    message: (string) ($json['error']['message'] ?? 'Unknown error'),
                    details: (array) ($json['error']['details'] ?? []),
                );
            }

            throw BogHttpException::fromResponse($response, $url);
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    /**
     * Build a PendingRequest with the configured authentication method.
     */
    private function buildPendingRequest(): PendingRequest
    {
        $authType = (string) ($this->config['auth_type'] ?? 'oauth2');
        $httpConfig = $this->config['http'] ?? [];
        $timeout = (int) ($httpConfig['timeout'] ?? 15);
        $retryTimes = (int) ($httpConfig['retry_times'] ?? 2);
        $retrySleepMs = (int) ($httpConfig['retry_sleep_ms'] ?? 250);
        $pending = $this->http->acceptJson()->asJson()->timeout($timeout)->retry($retryTimes, $retrySleepMs);

        return match ($authType) {
            'basic' => $pending->withBasicAuth(
                (string) ($this->config['client_id'] ?? ''),
                (string) ($this->config['client_secret'] ?? ''),
            ),
            'apikey' => $pending->withHeaders([
                'X-API-Key' => (string) ($this->config['api_key'] ?? ''),
            ]),
            'hmac-sha256' => $pending->withHeaders([
                'X-HMAC-Secret' => (string) ($this->config['hmac_secret'] ?? ''),
            ]),
            default => $pending->withToken($this->tokens->for('billing')),
        };
    }
}
