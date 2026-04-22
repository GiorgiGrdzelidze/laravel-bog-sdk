<?php

declare(strict_types=1);

namespace GiorgiGrdzelidze\BogSdk\Bonline\Dto;

/**
 * A single transaction record from a Bonline statement or today's activities.
 */
final readonly class TransactionDto
{
    public function __construct(
        public int $id,
        public string $entryDate,
        public string $entryDocumentNumber,
        public string $entryAccountNumber,
        public float $entryAmountDebit,
        public float $entryAmountCredit,
        public float $entryAmountBase,
        public float $entryAmount,
        public string $entryComment,
        public string $documentProductGroup,
        public string $documentValueDate,
        public string $documentOperationCode,
        public string $documentOperationType,
        public string $documentPayerName,
        public string $documentPayerInn,
        public string $documentPayerAccount,
        public string $documentBeneficiaryName,
        public string $documentBeneficiaryInn,
        public string $documentBeneficiaryAccount,
        public string $documentBeneficiaryBankCode,
        public string $documentBeneficiaryBankName,
        public string $documentNomination,
        public string $documentInformation,
        public string $documentAdditionalInformation,
        public string $documentSenderInstitution,
        public string $documentIntermediaryInstitution,
        public string $documentReceiverInstitution,
        public string $documentPayeeInn,
    ) {}

    /**
     * Create a TransactionDto from the Bonline API response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['Id'] ?? 0),
            entryDate: (string) ($data['EntryDate'] ?? ''),
            entryDocumentNumber: (string) ($data['EntryDocumentNumber'] ?? ''),
            entryAccountNumber: (string) ($data['EntryAccountNumber'] ?? ''),
            entryAmountDebit: (float) ($data['EntryAmountDebit'] ?? 0),
            entryAmountCredit: (float) ($data['EntryAmountCredit'] ?? 0),
            entryAmountBase: (float) ($data['EntryAmountBase'] ?? 0),
            entryAmount: (float) ($data['EntryAmount'] ?? 0),
            entryComment: (string) ($data['EntryComment'] ?? ''),
            documentProductGroup: (string) ($data['DocumentProductGroup'] ?? ''),
            documentValueDate: (string) ($data['DocumentValueDate'] ?? ''),
            documentOperationCode: (string) ($data['DocumentOperationCode'] ?? ''),
            documentOperationType: (string) ($data['DocumentOperationType'] ?? ''),
            documentPayerName: (string) ($data['DocumentPayerName'] ?? ''),
            documentPayerInn: (string) ($data['DocumentPayerInn'] ?? ''),
            documentPayerAccount: (string) ($data['DocumentPayerAccount'] ?? ''),
            documentBeneficiaryName: (string) ($data['DocumentBeneficiaryName'] ?? ''),
            documentBeneficiaryInn: (string) ($data['DocumentBeneficiaryInn'] ?? ''),
            documentBeneficiaryAccount: (string) ($data['DocumentBeneficiaryAccount'] ?? ''),
            documentBeneficiaryBankCode: (string) ($data['DocumentBeneficiaryBankCode'] ?? ''),
            documentBeneficiaryBankName: (string) ($data['DocumentBeneficiaryBankName'] ?? ''),
            documentNomination: (string) ($data['DocumentNomination'] ?? ''),
            documentInformation: (string) ($data['DocumentInformation'] ?? ''),
            documentAdditionalInformation: (string) ($data['DocumentAdditionalInformation'] ?? ''),
            documentSenderInstitution: (string) ($data['DocumentSenderInstitution'] ?? ''),
            documentIntermediaryInstitution: (string) ($data['DocumentIntermediaryInstitution'] ?? ''),
            documentReceiverInstitution: (string) ($data['DocumentReceiverInstitution'] ?? ''),
            documentPayeeInn: (string) ($data['DocumentPayeeInn'] ?? ''),
        );
    }

    /**
     * Convert the DTO to an array matching the Bonline API format.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'Id' => $this->id,
            'EntryDate' => $this->entryDate,
            'EntryDocumentNumber' => $this->entryDocumentNumber,
            'EntryAccountNumber' => $this->entryAccountNumber,
            'EntryAmountDebit' => $this->entryAmountDebit,
            'EntryAmountCredit' => $this->entryAmountCredit,
            'EntryAmountBase' => $this->entryAmountBase,
            'EntryAmount' => $this->entryAmount,
            'EntryComment' => $this->entryComment,
            'DocumentProductGroup' => $this->documentProductGroup,
            'DocumentValueDate' => $this->documentValueDate,
            'DocumentOperationCode' => $this->documentOperationCode,
            'DocumentOperationType' => $this->documentOperationType,
            'DocumentPayerName' => $this->documentPayerName,
            'DocumentPayerInn' => $this->documentPayerInn,
            'DocumentPayerAccount' => $this->documentPayerAccount,
            'DocumentBeneficiaryName' => $this->documentBeneficiaryName,
            'DocumentBeneficiaryInn' => $this->documentBeneficiaryInn,
            'DocumentBeneficiaryAccount' => $this->documentBeneficiaryAccount,
            'DocumentBeneficiaryBankCode' => $this->documentBeneficiaryBankCode,
            'DocumentBeneficiaryBankName' => $this->documentBeneficiaryBankName,
            'DocumentNomination' => $this->documentNomination,
            'DocumentInformation' => $this->documentInformation,
            'DocumentAdditionalInformation' => $this->documentAdditionalInformation,
            'DocumentSenderInstitution' => $this->documentSenderInstitution,
            'DocumentIntermediaryInstitution' => $this->documentIntermediaryInstitution,
            'DocumentReceiverInstitution' => $this->documentReceiverInstitution,
            'DocumentPayeeInn' => $this->documentPayeeInn,
        ];
    }
}
