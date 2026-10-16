<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Tests\Unit;

use GiorgiGrdzelidze\BogSdk\Bonline\Dto\CardPaymentDto;
use GiorgiGrdzelidze\BogSdk\Bonline\Dto\TransactionDto;
use GiorgiGrdzelidze\BogSdk\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class CardPaymentTest extends TestCase
{
    private const CURRENT = 'გადახდა - თარიღი: 15/10/2026 12:40:30; თანხა:GEL 254.45; საკომისიო: GEL 3.82; ბარათი: MC, 548888***8236; კოდი: 555349; ტერმინალის ID: POS1NB83; დეპოზ:POS1NB83R8Q; LUMINO;  ID:; მისამართი: ქავთარაძის 78';

    private const LEGACY = 'გადახდა - თარიღი: 16/10/2023 13:16:39; თანხა:GEL 13.9; საკომისიო: GEL 0.21; ბარათი: MC, 548888025***4506; კოდი: 894922; ტერმინალის ID: POS1NB83; დეპოზ:POS1NB83R9A; LUMINO; მისამართი: ქავთარაძის 78';

    public function test_current_format_is_parsed_field_by_field(): void
    {
        $payment = CardPaymentDto::fromText(self::CURRENT);

        $this->assertNotNull($payment);
        $this->assertSame('2026-10-15 12:40:30', $payment->purchasedAt->format('Y-m-d H:i:s'));
        $this->assertSame('GEL', $payment->currency);
        $this->assertSame(254.45, $payment->amount);
        $this->assertSame(3.82, $payment->commission);
        $this->assertSame(250.63, $payment->netAmount());
        $this->assertSame('POS1NB83', $payment->terminalId);
        $this->assertSame('MC, 548888***8236', $payment->card);
        $this->assertSame('555349', $payment->authCode);
        $this->assertSame('POS1NB83R8Q', $payment->depositId);
        $this->assertSame('LUMINO', $payment->merchant);
        $this->assertSame('ქავთარაძის 78', $payment->address);
        $this->assertSame([
            'purchased_at' => '2026-10-15 12:40:30', 'currency' => 'GEL', 'amount' => 254.45, 'commission' => 3.82, 'net_amount' => 250.63,
            'terminal_id' => 'POS1NB83', 'card' => 'MC, 548888***8236', 'auth_code' => '555349', 'deposit_id' => 'POS1NB83R8Q',
            'merchant' => 'LUMINO', 'address' => 'ქავთარაძის 78',
        ], $payment->toArray());
    }

    public function test_legacy_format_without_the_empty_id_segment(): void
    {
        $payment = CardPaymentDto::fromText(self::LEGACY);

        $this->assertSame('2023-10-16 13:16:39', $payment?->purchasedAt->format('Y-m-d H:i:s'));
        $this->assertSame(13.9, $payment->amount);
        $this->assertSame(13.69, $payment->netAmount());
        $this->assertSame('ქავთარაძის 78', $payment->address);
    }

    public function test_other_currencies_and_minimal_narratives(): void
    {
        $payment = CardPaymentDto::fromText('გადახდა - თარიღი: 01/02/2026 09:05:00; თანხა:USD 40; საკომისიო: USD 0.6; ტერმინალის ID: POS2XX01');

        $this->assertSame('USD', $payment?->currency);
        $this->assertSame(40.0, $payment->amount);
        $this->assertSame('POS2XX01', $payment->terminalId);
        $this->assertNull($payment->card);
        $this->assertNull($payment->merchant);
        $this->assertNull($payment->address);
    }

    /**
     * @return array<string, array{0: ?string}>
     */
    public static function notACardPayment(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'transfer' => ['საკუთარ ანგარიშზე გადარიცხვა საქართველოს ბანკში'],
            'cash deposit at terminal' => ['ტერმინალის საშუალებით ნავაჭრი თანხის შეტანა სალაროს ნაშთი'],
            'own card spent abroad' => ['გადახდა - თანხა: USD0.80; ობიექტი: FACEBK W7Z95XKAT2, Ireland; MCC:7311; თარიღი: 24/10/2023 00:00; ბარათის No: ****4494;'],
            'subscription' => ['გადახდა - თანხა: USD 12; MCC: 5734; მერჩანტის დასახელება: ZOHO'],
            'no terminal' => ['გადახდა - თარიღი: 15/10/2026 12:40:30; თანხა:GEL 254.45; საკომისიო: GEL 3.82; ბარათი: MC, 548888***8236'],
            'empty terminal' => ['გადახდა - თარიღი: 15/10/2026 12:40:30; თანხა:GEL 254.45; საკომისიო: GEL 3.82; ტერმინალის ID: '],
            'impossible date' => ['გადახდა - თარიღი: 32/13/2026 25:61:00; თანხა:GEL 1; საკომისიო: GEL 0.01; ტერმინალის ID: POS1'],
        ];
    }

    #[DataProvider('notACardPayment')]
    public function test_anything_else_is_not_a_card_payment(?string $text): void
    {
        $this->assertNull(CardPaymentDto::fromText($text));
    }

    public function test_statement_transactions_expose_their_card_payment(): void
    {
        $base = ['EntryId' => 1, 'EntryAmountCredit' => 250.63, 'EntryAmountDebit' => 0];

        $this->assertSame(254.45, TransactionDto::fromArray($base + ['DocumentNomination' => self::CURRENT])->cardPayment()?->amount);
        $this->assertSame(13.9, TransactionDto::fromArray($base + ['DocumentNomination' => null, 'EntryComment' => self::LEGACY])->cardPayment()?->amount);
        $this->assertNull(TransactionDto::fromArray(['EntryAmountCredit' => 0, 'EntryAmountDebit' => 12.0, 'DocumentNomination' => self::CURRENT])->cardPayment());
        $this->assertNull(TransactionDto::fromArray($base + ['DocumentNomination' => 'პროდუქციის საფასური'])->cardPayment());
    }
}
