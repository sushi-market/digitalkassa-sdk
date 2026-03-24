<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\CorrectionReceipt;

final readonly class CorrectionReceiptRequestDTO
{
    public function __construct(
        public string $receipt_id,
        public CorrectionReceiptDTO $correction_receipt,
    ) {}
}
