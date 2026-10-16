<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Dto;

use DateTimeImmutable;

/**
 * A card payment taken on one of your BOG POS terminals.
 *
 * Bonline does not expose these as structured fields: each payment arrives as its own credit whose
 * nomination (older entries: EntryComment) reads like
 * "გადახდა - თარიღი: 15/10/2026 12:40:30; თანხა:GEL 254.45; საკომისიო: GEL 3.82; ბარათი: MC, 548888***8236;
 *  კოდი: 555349; ტერმინალის ID: POS1NB83; დეპოზ:POS1NB83R8Q; LUMINO;  ID:; მისამართი: ქავთარაძის 78".
 * The credited amount is the gross amount minus the commission.
 */
final readonly class CardPaymentDto
{
    private const HEAD = '~^\s*გადახდა - თარიღი: (\d{2})/(\d{2})/(\d{4}) (\d{2}:\d{2}:\d{2}); თანხა:\s*([A-Z]{3})\s*([\d.]+); საკომისიო:\s*[A-Z]{3}\s*([\d.]+);~u';

    public function __construct(
        public DateTimeImmutable $purchasedAt,
        public string $currency,
        public float $amount,
        public float $commission,
        public string $terminalId,
        public ?string $card = null,
        public ?string $authCode = null,
        public ?string $depositId = null,
        public ?string $merchant = null,
        public ?string $address = null,
    ) {}

    /**
     * Parse a POS payment narrative; returns null for anything that is not a terminal card payment
     * (transfers, cash deposits, your own card spending abroad, …).
     */
    public static function fromText(?string $text): ?self
    {
        if ($text === null || ! preg_match(self::HEAD, $text, $m)) {
            return null;
        }

        $fields = [];
        $merchant = null;
        foreach (array_slice(array_map('trim', explode(';', $text)), 3) as $segment) {
            if ($segment === '') {
                continue;
            }

            if (preg_match('~^([^:]+):\s*(.*)$~u', $segment, $kv)) {
                $fields[trim($kv[1])] = trim($kv[2]);
            } elseif ($merchant === null) {
                $merchant = $segment;
            }
        }

        if (($fields['ტერმინალის ID'] ?? '') === '') {
            return null;
        }

        // createFromFormat() silently rolls 32/13 over into a valid date, so insist on an exact round trip.
        $stamp = "{$m[3]}-{$m[2]}-{$m[1]} {$m[4]}";
        $purchasedAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $stamp);
        if ($purchasedAt === false || $purchasedAt->format('Y-m-d H:i:s') !== $stamp) {
            return null;
        }

        return new self(
            purchasedAt: $purchasedAt,
            currency: $m[5],
            amount: (float) $m[6],
            commission: (float) $m[7],
            terminalId: $fields['ტერმინალის ID'],
            card: ($fields['ბარათი'] ?? '') !== '' ? $fields['ბარათი'] : null,
            authCode: ($fields['კოდი'] ?? '') !== '' ? $fields['კოდი'] : null,
            depositId: ($fields['დეპოზ'] ?? '') !== '' ? $fields['დეპოზ'] : null,
            merchant: $merchant,
            address: ($fields['მისამართი'] ?? '') !== '' ? $fields['მისამართი'] : null,
        );
    }

    /**
     * The amount actually credited to the account (gross amount minus the bank's commission).
     */
    public function netAmount(): float
    {
        return round($this->amount - $this->commission, 2);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'purchased_at' => $this->purchasedAt->format('Y-m-d H:i:s'),
            'currency' => $this->currency,
            'amount' => $this->amount,
            'commission' => $this->commission,
            'net_amount' => $this->netAmount(),
            'terminal_id' => $this->terminalId,
            'card' => $this->card,
            'auth_code' => $this->authCode,
            'deposit_id' => $this->depositId,
            'merchant' => $this->merchant,
            'address' => $this->address,
        ];
    }
}
