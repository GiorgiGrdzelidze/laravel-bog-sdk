<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Payments;

use GiorgiGrdzelidze\BogSdk\Exceptions\BogInvalidSignatureException;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\OrderCallbackDto;
use GiorgiGrdzelidze\BogSdk\Payments\Endpoints\ApplePayEndpoint;
use GiorgiGrdzelidze\BogSdk\Payments\Endpoints\CardChargesEndpoint;
use GiorgiGrdzelidze\BogSdk\Payments\Endpoints\GooglePayEndpoint;
use GiorgiGrdzelidze\BogSdk\Payments\Endpoints\OrdersEndpoint;
use GiorgiGrdzelidze\BogSdk\Payments\Endpoints\SplitPaymentEndpoint;
use GiorgiGrdzelidze\BogSdk\Support\SignatureVerifier;

/**
 * BOG Payments v1 API client.
 *
 * Provides access to e-commerce orders, card charges, split payments,
 * Apple Pay, Google Pay, and callback signature verification.
 */
final class PaymentsClient
{
    private ?OrdersEndpoint $ordersEndpoint = null;

    private ?CardChargesEndpoint $cardChargesEndpoint = null;

    private ?SplitPaymentEndpoint $splitPaymentEndpoint = null;

    private ?ApplePayEndpoint $applePayEndpoint = null;

    private ?GooglePayEndpoint $googlePayEndpoint = null;

    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
        private readonly SignatureVerifier $signatureVerifier,
    ) {}

    /**
     * Get the orders endpoint for creating, querying, refunding, and confirming orders.
     */
    public function orders(): OrdersEndpoint
    {
        return $this->ordersEndpoint ??= new OrdersEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the card charges endpoint for saved card and subscription charges.
     */
    public function cardCharges(): CardChargesEndpoint
    {
        return $this->cardChargesEndpoint ??= new CardChargesEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the split payment endpoint for distributing payments across accounts.
     */
    public function splitPayment(): SplitPaymentEndpoint
    {
        return $this->splitPaymentEndpoint ??= new SplitPaymentEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the Apple Pay endpoint for completing Apple Pay payments.
     */
    public function applePay(): ApplePayEndpoint
    {
        return $this->applePayEndpoint ??= new ApplePayEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Get the Google Pay endpoint for completing Google Pay payments.
     */
    public function googlePay(): GooglePayEndpoint
    {
        return $this->googlePayEndpoint ??= new GooglePayEndpoint($this->http, $this->baseUrl);
    }

    /**
     * Verify a BOG callback signature and return the parsed callback DTO.
     *
     * @param  string  $rawBody  The raw request body from the callback.
     * @param  string  $signatureHeader  The X-Signature header value (base64-encoded).
     *
     * @throws BogInvalidSignatureException If the signature is invalid or body is not JSON.
     */
    public function verifyCallback(string $rawBody, string $signatureHeader): OrderCallbackDto
    {
        $this->signatureVerifier->verifyOrFail($rawBody, $signatureHeader);

        $data = json_decode($rawBody, true);
        if (! is_array($data)) {
            throw new BogInvalidSignatureException('Callback body is not valid JSON.');
        }

        return OrderCallbackDto::fromArray($data);
    }
}
