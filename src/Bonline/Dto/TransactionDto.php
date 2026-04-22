<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Dto;

/**
 * A single transaction record from a Bonline statement.
 */
final readonly class TransactionDto
{
    /**
     * @param  array<string, mixed>  $senderDetails  Nested sender info (Name, Inn, AccountNumber, BankCode, BankName).
     * @param  array<string, mixed>  $beneficiaryDetails  Nested beneficiary info.
     */
    public function __construct(
        public string $entryDate,
        public string $entryDocumentNumber,
        public string $entryAccountNumber,
        public float $entryAmountDebit,
        public float $entryAmountDebitBase,
        public float $entryAmountCredit,
        public ?float $entryAmountCreditBase,
        public float $entryAmountBase,
        public float $entryAmount,
        public string $entryComment,
        public string $documentProductGroup,
        public string $documentValueDate,
        public array $senderDetails,
        public array $beneficiaryDetails,
        public ?string $documentTreasuryCode,
        public string $documentNomination,
        public string $documentInformation,
        public float $documentSourceAmount,
        public string $documentSourceCurrency,
        public float $documentDestinationAmount,
        public string $documentDestinationCurrency,
        public string $documentReceiveDate,
        public ?float $documentRate,
        public int $documentKey,
        public int $entryId,
        public string $documentPayerName,
        public string $documentPayerInn,
        public string $docComment,
        public ?string $authDate,
    ) {}

    /**
     * Create a TransactionDto from the Bonline API response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            entryDate: (string) ($data['EntryDate'] ?? ''),
            entryDocumentNumber: (string) ($data['EntryDocumentNumber'] ?? ''),
            entryAccountNumber: (string) ($data['EntryAccountNumber'] ?? ''),
            entryAmountDebit: (float) ($data['EntryAmountDebit'] ?? 0),
            entryAmountDebitBase: (float) ($data['EntryAmountDebitBase'] ?? 0),
            entryAmountCredit: (float) ($data['EntryAmountCredit'] ?? 0),
            entryAmountCreditBase: isset($data['EntryAmountCreditBase']) ? (float) $data['EntryAmountCreditBase'] : null,
            entryAmountBase: (float) ($data['EntryAmountBase'] ?? 0),
            entryAmount: (float) ($data['EntryAmount'] ?? 0),
            entryComment: (string) ($data['EntryComment'] ?? ''),
            documentProductGroup: (string) ($data['DocumentProductGroup'] ?? ''),
            documentValueDate: (string) ($data['DocumentValueDate'] ?? ''),
            senderDetails: (array) ($data['SenderDetails'] ?? []),
            beneficiaryDetails: (array) ($data['BeneficiaryDetails'] ?? []),
            documentTreasuryCode: $data['DocumentTreasuryCode'] ?? null,
            documentNomination: (string) ($data['DocumentNomination'] ?? ''),
            documentInformation: (string) ($data['DocumentInformation'] ?? ''),
            documentSourceAmount: (float) ($data['DocumentSourceAmount'] ?? 0),
            documentSourceCurrency: (string) ($data['DocumentSourceCurrency'] ?? ''),
            documentDestinationAmount: (float) ($data['DocumentDestinationAmount'] ?? 0),
            documentDestinationCurrency: (string) ($data['DocumentDestinationCurrency'] ?? ''),
            documentReceiveDate: (string) ($data['DocumentReceiveDate'] ?? ''),
            documentRate: isset($data['DocumentRate']) ? (float) $data['DocumentRate'] : null,
            documentKey: (int) ($data['DocumentKey'] ?? 0),
            entryId: (int) ($data['EntryId'] ?? 0),
            documentPayerName: (string) ($data['DocumentPayerName'] ?? ''),
            documentPayerInn: (string) ($data['DocumentPayerInn'] ?? ''),
            docComment: (string) ($data['DocComment'] ?? ''),
            authDate: $data['AuthDate'] ?? null,
        );
    }

    /**
     * Get the sender name (from nested SenderDetails).
     */
    public function senderName(): string
    {
        return (string) ($this->senderDetails['Name'] ?? '');
    }

    /**
     * Get the beneficiary name (from nested BeneficiaryDetails).
     */
    public function beneficiaryName(): string
    {
        return (string) ($this->beneficiaryDetails['Name'] ?? '');
    }

    /**
     * Convert the DTO to an array matching the Bonline API format.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'EntryDate' => $this->entryDate,
            'EntryDocumentNumber' => $this->entryDocumentNumber,
            'EntryAccountNumber' => $this->entryAccountNumber,
            'EntryAmountDebit' => $this->entryAmountDebit,
            'EntryAmountDebitBase' => $this->entryAmountDebitBase,
            'EntryAmountCredit' => $this->entryAmountCredit,
            'EntryAmountCreditBase' => $this->entryAmountCreditBase,
            'EntryAmountBase' => $this->entryAmountBase,
            'EntryAmount' => $this->entryAmount,
            'EntryComment' => $this->entryComment,
            'DocumentProductGroup' => $this->documentProductGroup,
            'DocumentValueDate' => $this->documentValueDate,
            'SenderDetails' => $this->senderDetails,
            'BeneficiaryDetails' => $this->beneficiaryDetails,
            'DocumentTreasuryCode' => $this->documentTreasuryCode,
            'DocumentNomination' => $this->documentNomination,
            'DocumentInformation' => $this->documentInformation,
            'DocumentSourceAmount' => $this->documentSourceAmount,
            'DocumentSourceCurrency' => $this->documentSourceCurrency,
            'DocumentDestinationAmount' => $this->documentDestinationAmount,
            'DocumentDestinationCurrency' => $this->documentDestinationCurrency,
            'DocumentReceiveDate' => $this->documentReceiveDate,
            'DocumentRate' => $this->documentRate,
            'DocumentKey' => $this->documentKey,
            'EntryId' => $this->entryId,
            'DocumentPayerName' => $this->documentPayerName,
            'DocumentPayerInn' => $this->documentPayerInn,
            'DocComment' => $this->docComment,
            'AuthDate' => $this->authDate,
        ];
    }
}
