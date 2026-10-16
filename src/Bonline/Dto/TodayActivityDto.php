<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Dto;

/**
 * A movement on the account today, from documents/v2/todayactivities.
 *
 * This feed is near-live (statements only cover closed days) and uses its own field names —
 * Credit/Debit/Nomination/PostDate — not the statement's EntryAmountCredit/DocumentNomination/EntryDate.
 */
final readonly class TodayActivityDto
{
    /**
     * @param  array<string, mixed>  $sender  Name, Inn, AccountNumber, BankCode, BankName.
     * @param  array<string, mixed>  $beneficiary  Same shape as $sender.
     * @param  array<string, mixed>  $rawData  The untouched record.
     */
    public function __construct(
        public int $id,
        public int $docKey,
        public string $docNo,
        public string $postDate,
        public ?string $valueDate,
        public string $entryType,
        public string $entryComment,
        public string $nomination,
        public float $credit,
        public float $debit,
        public float $amount,
        public float $amountBase,
        public string $payerName,
        public string $payerInn,
        public array $sender,
        public array $beneficiary,
        public array $rawData = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $valueDate = (string) ($data['ValueDate'] ?? '');

        return new self(
            id: (int) ($data['Id'] ?? 0),
            docKey: (int) ($data['DocKey'] ?? 0),
            docNo: (string) ($data['DocNo'] ?? ''),
            postDate: (string) ($data['PostDate'] ?? ''),
            valueDate: $valueDate === '' || str_starts_with($valueDate, '0001-01-01') ? null : $valueDate,
            entryType: (string) ($data['EntryType'] ?? ''),
            entryComment: (string) ($data['EntryComment'] ?? ''),
            nomination: (string) ($data['Nomination'] ?? ''),
            credit: (float) ($data['Credit'] ?? 0),
            debit: (float) ($data['Debit'] ?? 0),
            amount: (float) ($data['Amount'] ?? 0),
            amountBase: (float) ($data['AmountBase'] ?? 0),
            payerName: (string) ($data['PayerName'] ?? ''),
            payerInn: (string) ($data['PayerInn'] ?? ''),
            sender: is_array($data['Sender'] ?? null) ? $data['Sender'] : [],
            beneficiary: is_array($data['Beneficiary'] ?? null) ? $data['Beneficiary'] : [],
            rawData: $data,
        );
    }

    public function isCredit(): bool
    {
        return $this->credit > 0;
    }

    /**
     * The POS card payment behind this credit, if it is one.
     */
    public function cardPayment(): ?CardPaymentDto
    {
        return $this->isCredit() ? CardPaymentDto::fromText($this->nomination !== '' ? $this->nomination : $this->entryComment) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->rawData !== [] ? $this->rawData : [
            'Id' => $this->id,
            'DocKey' => $this->docKey,
            'DocNo' => $this->docNo,
            'PostDate' => $this->postDate,
            'ValueDate' => $this->valueDate,
            'EntryType' => $this->entryType,
            'EntryComment' => $this->entryComment,
            'Nomination' => $this->nomination,
            'Credit' => $this->credit,
            'Debit' => $this->debit,
            'Amount' => $this->amount,
            'AmountBase' => $this->amountBase,
            'PayerName' => $this->payerName,
            'PayerInn' => $this->payerInn,
            'Sender' => $this->sender,
            'Beneficiary' => $this->beneficiary,
        ];
    }
}
