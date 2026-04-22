# Changelog

All notable changes to `laravel-bog-sdk` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-04-22

### Added
- **Business Online (Bonline)**: Balance, Statement (with auto-pagination), Today Activities, Summary, Accounts, Currency Rates, Requisites
- **Payments v1**: Orders (create, get, refund, cancel, confirm), Saved Card Charges, Subscription Charges, Split Payments, Apple Pay, Google Pay
- **iPay (legacy)**: Orders, Refunds, Subscriptions, Pre-auth
- **Installment**: Calculator, Checkout, Order Details, JS Config helper, Validation rules
- **Billing**: Payments, Status, Cancel, Payment Info (OAuth2/Basic/API Key/HMAC auth)
- **BOG-ID**: OpenID Connect redirect, code exchange, userinfo
- **Open Banking**: Identity Assurance
- **Infrastructure**: OAuth2 TokenManager with per-domain caching, HttpClient with 401 auto-retry, RSA SHA256 signature verification, 14 typed exceptions, 8 enums, 31 DTOs
- **Testing**: 110 tests, 315 assertions
- **Multi-account**: Support for multiple IBAN accounts via `BOG_BONLINE_ACCOUNTS`
- **Multi-currency**: Support for multiple currencies via `BOG_BONLINE_CURRENCIES`
- **CI**: GitHub Actions with PHP 8.3/8.4 matrix, PHPStan level 6, Pint code style
