# Changelog

All notable changes to `laravel-bog-sdk` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [1.0.2] - 2026-06-22

### Fixed
- **Non-idempotent retry**: transport-level retry on `ConnectionException` is now limited to idempotent `GET` requests. A `POST`/`PUT`/`PATCH`/`DELETE` (create order, refund) may already have reached BOG before the connection dropped, so it is no longer re-sent — preventing duplicate orders and double refunds on a slow response.
- **Callback parsing robustness**: `OrderCallbackDto` and `OrderDetailsDto` guard `order_status` with `is_array` before indexing `['key']`, so an unexpected payload shape no longer throws a `TypeError`.
- **Token cache store**: `BOG_TOKEN_CACHE_STORE` is now honoured — the `TokenManager` resolves the configured cache store instead of always using the default.

## [1.0.1] - 2026-06-22

### Fixed
- **Payments callback parsing**: `OrderCallbackDto` now unwraps the top-level `body` key that BOG wraps the order payload in, and reads the correct field names (`order_id`, `order_status.key`, `purchase_units.request_amount`/`transfer_amount`, `currency_code`). Previously every verified callback returned an empty DTO.
- **Order details parsing**: `OrderDetailsDto` (`GET /receipt/{order_id}`) now maps the real response fields — `order_id`, `currency_code`, `request_amount`/`transfer_amount`, `payment_detail.transfer_method.key`, `payer_identifier`, `request_rrn`.
- **HTTP retry**: transport retries are now limited to connection failures, so non-idempotent payment POSTs are never silently re-sent on a 4xx/5xx response.
- **README**: corrected the callback signature header to `Callback-Signature` (was `X-Signature`).

### Added
- `OrderCallbackDto` exposes `event`, `zonedRequestTime`, `requestAmount`, `transferAmount`.
- `OrderDetailsDto` exposes `requestAmount`, `transferAmount`, `refundAmount`.
- `BOG_PAYMENTS_ENV=sandbox` switches the Payments API and token hosts to the `*-sandbox.bog.ge` endpoints automatically; explicit `BOG_PAYMENTS_BASE_URL`/`BOG_PAYMENTS_TOKEN_URL` still override.
- README sandbox section with host table and test cards.

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
