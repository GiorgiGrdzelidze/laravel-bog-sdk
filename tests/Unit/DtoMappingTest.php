<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\Auth\Dto\AccessToken;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\CancelResponseDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentInfoDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentRequestDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentResponseDto;
use GiorgiGrdzelidze\BogSdk\Billing\Dto\PaymentStatusDto;
use GiorgiGrdzelidze\BogSdk\BogId\Dto\BogIdTokenDto;
use GiorgiGrdzelidze\BogSdk\BogId\Dto\BogIdUserDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\AccountDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\BalanceDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\CurrencyRateDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\StatementPageDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\SummaryDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\TransactionDto;
use GiorgiGrdzelidze\BogSdk\Installment\Dto\DiscountDto;
use GiorgiGrdzelidze\BogSdk\Installment\Dto\InstallmentBasketItemDto;
use GiorgiGrdzelidze\BogSdk\Installment\Dto\InstallmentOrderDetailsDto;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayItemDto;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayOrderResponseDto;
use GiorgiGrdzelidze\BogSdk\IPay\Dto\IPayPaymentDetailsDto;
use GiorgiGrdzelidze\BogSdk\OpenBanking\Identity\Dto\IdentityAssuranceDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\BasketItemDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\BuyerDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\CreateOrderResponseDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\OrderCallbackDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\OrderDetailsDto;
use GiorgiGrdzelidze\BogSdk\Payments\Dto\SplitAccountDto;
use PHPUnit\Framework\TestCase;

final class DtoMappingTest extends TestCase
{
    public function test_access_token_roundtrip(): void
    {
        $data = ['access_token' => 'abc', 'expires_in' => 3600, 'token_type' => 'Bearer'];
        $dto = AccessToken::fromArray($data);
        $this->assertSame('abc', $dto->accessToken);
        $this->assertSame(3600, $dto->expiresIn);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_transaction_dto_roundtrip(): void
    {
        $data = [
            'Id' => 123, 'EntryDate' => '2025-01-01T00:00:00',
            'EntryDocumentNumber' => 'DOC1', 'EntryAccountNumber' => 'GE00',
            'EntryAmountDebit' => 10.5, 'EntryAmountCredit' => 0,
            'EntryAmountBase' => 10.5, 'EntryAmount' => 10.5,
            'EntryComment' => 'Test', 'DocumentProductGroup' => 'TRF',
            'DocumentValueDate' => '2025-01-01', 'DocumentOperationCode' => 'OUT',
            'DocumentOperationType' => 'TRANSFER', 'DocumentPayerName' => 'Payer',
            'DocumentPayerInn' => '111', 'DocumentPayerAccount' => 'GE01',
            'DocumentBeneficiaryName' => 'Ben', 'DocumentBeneficiaryInn' => '222',
            'DocumentBeneficiaryAccount' => 'GE02', 'DocumentBeneficiaryBankCode' => 'BAGAGE22',
            'DocumentBeneficiaryBankName' => 'BOG', 'DocumentNomination' => 'Pay',
            'DocumentInformation' => 'Info', 'DocumentAdditionalInformation' => 'More',
            'DocumentSenderInstitution' => 'S', 'DocumentIntermediaryInstitution' => 'I',
            'DocumentReceiverInstitution' => 'R', 'DocumentPayeeInn' => '333',
        ];

        $dto = TransactionDto::fromArray($data);
        $this->assertSame(123, $dto->id);
        $this->assertSame('Test', $dto->entryComment);
        $this->assertEquals($data, $dto->toArray());
    }

    public function test_statement_page_dto(): void
    {
        $data = [
            'Id' => 'cursor-1',
            'RecordCount' => 1,
            'Records' => [
                ['Id' => 1, 'EntryDate' => '', 'EntryDocumentNumber' => '', 'EntryAccountNumber' => '', 'EntryAmountDebit' => 0, 'EntryAmountCredit' => 0, 'EntryAmountBase' => 0, 'EntryAmount' => 0, 'EntryComment' => '', 'DocumentProductGroup' => '', 'DocumentValueDate' => '', 'DocumentOperationCode' => '', 'DocumentOperationType' => '', 'DocumentPayerName' => '', 'DocumentPayerInn' => '', 'DocumentPayerAccount' => '', 'DocumentBeneficiaryName' => '', 'DocumentBeneficiaryInn' => '', 'DocumentBeneficiaryAccount' => '', 'DocumentBeneficiaryBankCode' => '', 'DocumentBeneficiaryBankName' => '', 'DocumentNomination' => '', 'DocumentInformation' => '', 'DocumentAdditionalInformation' => '', 'DocumentSenderInstitution' => '', 'DocumentIntermediaryInstitution' => '', 'DocumentReceiverInstitution' => '', 'DocumentPayeeInn' => ''],
            ],
        ];

        $dto = StatementPageDto::fromArray($data);
        $this->assertSame('cursor-1', $dto->id);
        $this->assertCount(1, $dto->records);
        $this->assertInstanceOf(TransactionDto::class, $dto->records[0]);
    }

    public function test_summary_dto_roundtrip(): void
    {
        $data = ['OpeningBalance' => 100.0, 'ClosingBalance' => 200.0, 'DebitTurnover' => 50.0, 'CreditTurnover' => 150.0, 'Currency' => 'GEL'];
        $dto = SummaryDto::fromArray($data);
        $this->assertSame(100.0, $dto->openingBalance);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_balance_dto_roundtrip(): void
    {
        $data = ['AccountNumber' => 'GE00', 'Currency' => 'GEL', 'AvailableBalance' => 500.0, 'CurrentBalance' => 500.0, 'BlockedAmount' => 0.0];
        $dto = BalanceDto::fromArray($data);
        $this->assertSame('GE00', $dto->accountNumber);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_account_dto_from_array(): void
    {
        $data = [
            'AccountNumber' => 'GE29BG0000000111111111',
            'Currency' => 'GEL',
            'AccountName' => 'Main Account',
            'AccountType' => 'CURRENT',
            'AvailableBalance' => 5000.50,
            'CurrentBalance' => 5100.00,
            'Status' => 'ACTIVE',
        ];

        $dto = AccountDto::fromArray($data);
        $this->assertSame('GE29BG0000000111111111', $dto->accountNumber);
        $this->assertSame('GEL', $dto->currency);
        $this->assertSame('Main Account', $dto->accountName);
        $this->assertSame('CURRENT', $dto->accountType);
        $this->assertSame(5000.50, $dto->availableBalance);
        $this->assertSame(5100.00, $dto->currentBalance);
        $this->assertSame('GE29BG0000000111111111', $dto->iban);
        $this->assertSame('ACTIVE', $dto->status);
        $this->assertSame($data, $dto->rawData);
    }

    public function test_account_dto_handles_lowercase_keys(): void
    {
        $data = [
            'accountNumber' => 'GE29BG0000000111111111',
            'currency' => 'USD',
            'accountName' => 'USD Account',
            'accountType' => 'SAVINGS',
        ];

        $dto = AccountDto::fromArray($data);
        $this->assertSame('GE29BG0000000111111111', $dto->accountNumber);
        $this->assertSame('USD', $dto->currency);
        $this->assertNull($dto->availableBalance);
    }

    public function test_currency_rate_dto_from_array(): void
    {
        $data = [
            'FromCurrency' => 'USD',
            'ToCurrency' => 'GEL',
            'BuyRate' => 2.65,
            'SellRate' => 2.72,
            'NbgRate' => 2.68,
            'Date' => '2026-04-22',
        ];

        $dto = CurrencyRateDto::fromArray($data);
        $this->assertSame('USD', $dto->fromCurrency);
        $this->assertSame('GEL', $dto->toCurrency);
        $this->assertSame(2.65, $dto->buyRate);
        $this->assertSame(2.72, $dto->sellRate);
        $this->assertSame(2.68, $dto->nbgRate);
        $this->assertSame('2026-04-22', $dto->date);
        $this->assertSame($data, $dto->rawData);
    }

    public function test_basket_item_dto_roundtrip(): void
    {
        $data = ['product_id' => 'p1', 'description' => 'Test', 'quantity' => 2, 'unit_price' => 10.5];
        $dto = BasketItemDto::fromArray($data);
        $this->assertSame('p1', $dto->productId);
        $this->assertSame(2, $dto->quantity);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_buyer_dto_excludes_nulls(): void
    {
        $dto = new BuyerDto(fullName: 'Test');
        $array = $dto->toArray();
        $this->assertSame(['full_name' => 'Test'], $array);
    }

    public function test_buyer_dto_includes_all_fields(): void
    {
        $dto = new BuyerDto('Test', 'test@example.com', '+995599000000');
        $array = $dto->toArray();
        $this->assertSame('Test', $array['full_name']);
        $this->assertSame('test@example.com', $array['email']);
        $this->assertSame('+995599000000', $array['phone_number']);
    }

    public function test_create_order_response_dto(): void
    {
        $data = [
            'id' => 'order-1',
            '_links' => ['redirect' => ['href' => 'https://pay.bog.ge/1'], 'details' => ['href' => 'https://api.bog.ge/1']],
        ];
        $dto = CreateOrderResponseDto::fromArray($data);
        $this->assertSame('order-1', $dto->id);
        $this->assertSame('https://pay.bog.ge/1', $dto->redirectUrl);
    }

    public function test_order_details_dto(): void
    {
        $data = [
            'id' => 'o1',
            'order_status' => ['key' => 'completed'],
            'purchase_units' => ['total_amount' => 99, 'currency' => 'GEL'],
            'payment_detail' => ['payment_method' => 'card', 'card_mask' => '4***9999', 'rrn' => '111'],
        ];
        $dto = OrderDetailsDto::fromArray($data);
        $this->assertSame('completed', $dto->statusKey);
        $this->assertSame('4***9999', $dto->cardMask);
    }

    public function test_order_callback_dto(): void
    {
        $data = [
            'id' => 'o1',
            'order_status' => ['key' => 'completed'],
            'external_order_id' => 'EXT-1',
            'purchase_units' => ['total_amount' => 50, 'currency' => 'GEL'],
        ];
        $dto = OrderCallbackDto::fromArray($data);
        $this->assertSame('completed', $dto->statusKey);
        $this->assertSame(50.0, $dto->totalAmount);
    }

    public function test_split_account_dto_roundtrip(): void
    {
        $data = ['account_number' => 'GE00', 'amount' => 25.5];
        $dto = SplitAccountDto::fromArray($data);
        $this->assertSame('GE00', $dto->accountNumber);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_ipay_item_dto_roundtrip(): void
    {
        $data = ['product_id' => 'sku-1', 'description' => 'Test item', 'quantity' => 3, 'unit_price' => 15.50];
        $dto = IPayItemDto::fromArray($data);
        $this->assertSame('sku-1', $dto->productId);
        $this->assertSame(3, $dto->quantity);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_ipay_order_response_dto(): void
    {
        $data = ['order_id' => 'ipay-1', 'redirect_url' => 'https://ipay.ge/pay/1', 'status' => 'created'];
        $dto = IPayOrderResponseDto::fromArray($data);
        $this->assertSame('ipay-1', $dto->orderId);
        $this->assertSame('https://ipay.ge/pay/1', $dto->redirectUrl);
        $this->assertSame('created', $dto->status);
    }

    public function test_ipay_payment_details_dto(): void
    {
        $data = ['order_id' => 'ipay-1', 'status' => 'completed', 'amount' => 25.00, 'currency' => 'GEL'];
        $dto = IPayPaymentDetailsDto::fromArray($data);
        $this->assertSame('ipay-1', $dto->orderId);
        $this->assertSame(25.00, $dto->amount);
    }

    public function test_installment_basket_item_dto_roundtrip(): void
    {
        $data = ['product_id' => 'p1', 'total_item_amount' => 200.00, 'total_item_qty' => 2];
        $dto = InstallmentBasketItemDto::fromArray($data);
        $this->assertSame('p1', $dto->productId);
        $this->assertSame(200.00, $dto->totalItemAmount);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_discount_dto_roundtrip(): void
    {
        $data = ['code' => 'DISC01', 'description' => '3m 0%', 'amount' => 0.0, 'month' => 3];
        $dto = DiscountDto::fromArray($data);
        $this->assertSame('DISC01', $dto->code);
        $this->assertSame(3, $dto->month);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_installment_order_details_dto(): void
    {
        $data = ['order_id' => 'inst-1', 'status' => 'approved', 'extra' => 'data'];
        $dto = InstallmentOrderDetailsDto::fromArray($data);
        $this->assertSame('inst-1', $dto->orderId);
        $this->assertSame('approved', $dto->status);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_bog_id_token_dto_roundtrip(): void
    {
        $data = ['access_token' => 'at', 'id_token' => 'it', 'expires_in' => 300, 'refresh_token' => 'rt', 'token_type' => 'Bearer'];
        $dto = BogIdTokenDto::fromArray($data);
        $this->assertSame('at', $dto->accessToken);
        $this->assertSame('it', $dto->idToken);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_bog_id_user_dto(): void
    {
        $data = ['sub' => 'u1', 'name' => 'Test', 'given_name' => 'T', 'family_name' => 'U', 'email' => 'e@e.com', 'email_verified' => true, 'phone_number' => '+995', 'personal_number' => '01001012345'];
        $dto = BogIdUserDto::fromArray($data);
        $this->assertSame('u1', $dto->sub);
        $this->assertSame('Test', $dto->name);
        $this->assertTrue($dto->emailVerified);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_identity_assurance_dto_roundtrip(): void
    {
        $data = ['verified' => true, 'first_name' => 'Giorgi', 'last_name' => 'G', 'confidence' => 'HIGH'];
        $dto = IdentityAssuranceDto::fromArray($data);
        $this->assertTrue($dto->verified);
        $this->assertSame('Giorgi', $dto->firstName);
        $this->assertSame($data, $dto->toArray());
    }

    public function test_billing_payment_request_dto(): void
    {
        $dto = new PaymentRequestDto(100.00, 'GEL', 'Test', 'EXT-1', ['key' => 'val']);
        $array = $dto->toArray();
        $this->assertSame(100.00, $array['amount']);
        $this->assertSame('EXT-1', $array['external_id']);
        $this->assertSame(['key' => 'val'], $array['metadata']);
    }

    public function test_billing_payment_request_dto_excludes_nulls(): void
    {
        $dto = new PaymentRequestDto(50.00, 'GEL', 'Test');
        $array = $dto->toArray();
        $this->assertArrayNotHasKey('external_id', $array);
        $this->assertArrayNotHasKey('metadata', $array);
    }

    public function test_billing_payment_response_dto(): void
    {
        $data = ['payment_id' => 'p1', 'status' => 'pending', 'redirect_url' => 'https://pay.bog.ge/1'];
        $dto = PaymentResponseDto::fromArray($data);
        $this->assertSame('p1', $dto->paymentId);
        $this->assertSame('pending', $dto->status);
        $this->assertSame('https://pay.bog.ge/1', $dto->redirectUrl);
    }

    public function test_billing_payment_status_dto(): void
    {
        $data = ['payment_id' => 'p1', 'status' => 'completed'];
        $dto = PaymentStatusDto::fromArray($data);
        $this->assertSame('p1', $dto->paymentId);
        $this->assertSame('completed', $dto->status);
    }

    public function test_billing_cancel_response_dto(): void
    {
        $data = ['payment_id' => 'p1', 'status' => 'cancelled'];
        $dto = CancelResponseDto::fromArray($data);
        $this->assertSame('p1', $dto->paymentId);
        $this->assertSame('cancelled', $dto->status);
    }

    public function test_billing_payment_info_dto(): void
    {
        $dto = new PaymentInfoDto('p1', ['note' => 'test']);
        $array = $dto->toArray();
        $this->assertSame('p1', $array['payment_id']);
        $this->assertSame(['note' => 'test'], $array['info']);
    }
}
