<?php

declare(strict_types=1);

namespace App\Domain\ReceiptData\DTO;

use App\Domain\ReceiptData\DTO\Concerns\HasExtraFields;

/**
 * `receipt` — folio y totales del recibo (contrato §2).
 */
final readonly class ReceiptInfoData
{
    use HasExtraFields;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $receiptId = null,
        public ?string $issueDate = null,
        public ?string $dueDate = null,
        public int|float|null $totalAmountMxn = null,
        public array $extra = [],
    ) {}

    /**
     * @param  array<string, mixed>|null  $raw
     */
    public static function fromArray(?array $raw): self
    {
        $raw ??= [];

        $known = ['receipt_id', 'issue_date', 'due_date', 'total_amount_mxn'];

        return new self(
            receiptId: isset($raw['receipt_id']) ? (string) $raw['receipt_id'] : null,
            issueDate: isset($raw['issue_date']) ? (string) $raw['issue_date'] : null,
            dueDate: isset($raw['due_date']) ? (string) $raw['due_date'] : null,
            totalAmountMxn: $raw['total_amount_mxn'] ?? null,
            extra: self::captureExtra($raw, $known),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::mergeExtra([
            'receipt_id' => $this->receiptId,
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
            'total_amount_mxn' => $this->totalAmountMxn,
        ], $this->extra);
    }
}
