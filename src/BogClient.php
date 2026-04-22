<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk;

use GiorgiGrdzelidze\BogSdk\Billing\BillingClient;
use GiorgiGrdzelidze\BogSdk\BogId\BogIdClient;
use GiorgiGrdzelidze\BogSdk\Bonline\BonlineClient;
use GiorgiGrdzelidze\BogSdk\Http\HttpClient;
use GiorgiGrdzelidze\BogSdk\Installment\InstallmentClient;
use GiorgiGrdzelidze\BogSdk\IPay\IPayClient;
use GiorgiGrdzelidze\BogSdk\OpenBanking\OpenBankingClient;
use GiorgiGrdzelidze\BogSdk\Payments\PaymentsClient;
use GiorgiGrdzelidze\BogSdk\Support\SignatureVerifier;
use Illuminate\Http\Client\Factory;

/**
 * Main entry point for the BOG SDK.
 *
 * Provides lazy-loaded access to all BOG API product clients:
 * Bonline, Payments, Billing, iPay, Installment, BOG-ID, and Open Banking.
 */
final class BogClient
{
    private ?BonlineClient $bonlineClient = null;

    private ?PaymentsClient $paymentsClient = null;

    private ?BillingClient $billingClient = null;

    private ?IPayClient $ipayClient = null;

    private ?InstallmentClient $installmentClient = null;

    private ?BogIdClient $bogIdClient = null;

    private ?OpenBankingClient $openBankingClient = null;

    /**
     * @param  array<string, mixed>  $config  Resolved bog-sdk config array.
     */
    public function __construct(
        private readonly HttpClient $http,
        private readonly Factory $httpFactory,
        private readonly array $config,
    ) {}

    /**
     * Get the Business Online (Bonline) client for accounts, statements, and rates.
     */
    public function bonline(): BonlineClient
    {
        return $this->bonlineClient ??= new BonlineClient(
            $this->http,
            rtrim((string) ($this->config['bonline']['base_url'] ?? ''), '/'),
        );
    }

    /**
     * Get the Payments v1 client for e-commerce orders, refunds, and card operations.
     */
    public function payments(): PaymentsClient
    {
        return $this->paymentsClient ??= new PaymentsClient(
            $this->http,
            rtrim((string) ($this->config['payments']['base_url'] ?? ''), '/'),
            new SignatureVerifier(
                (string) ($this->config['payments']['callback_public_key_path'] ?? ''),
            ),
        );
    }

    /**
     * Get the Billing client for payment creation, status, and cancellation.
     */
    public function billing(): BillingClient
    {
        return $this->billingClient ??= new BillingClient(
            $this->httpFactory,
            $this->http->tokens(),
            array_merge(
                (array) ($this->config['billing'] ?? []),
                ['http' => $this->config['http'] ?? []],
            ),
        );
    }

    /**
     * Get the iPay (legacy) client for checkout orders and subscriptions.
     */
    public function ipay(): IPayClient
    {
        return $this->ipayClient ??= new IPayClient(
            $this->http,
            rtrim((string) ($this->config['ipay']['base_url'] ?? ''), '/'),
        );
    }

    /**
     * Get the Installment client for calculator, checkout, and JS SDK config.
     */
    public function installment(): InstallmentClient
    {
        return $this->installmentClient ??= new InstallmentClient(
            $this->http,
            rtrim((string) ($this->config['installment']['base_url'] ?? ''), '/'),
            (string) ($this->config['installment']['shop_id'] ?? ''),
        );
    }

    /**
     * Get the BOG-ID (OpenID Connect) client for SSO authentication.
     */
    public function bogId(): BogIdClient
    {
        return $this->bogIdClient ??= new BogIdClient(
            $this->httpFactory,
            (array) ($this->config['bog_id'] ?? []),
        );
    }

    /**
     * Get the Open Banking client for identity assurance and PSD2.
     */
    public function openBanking(): OpenBankingClient
    {
        return $this->openBankingClient ??= new OpenBankingClient(
            $this->http,
            rtrim((string) ($this->config['open_banking']['base_url'] ?? ''), '/'),
        );
    }
}
