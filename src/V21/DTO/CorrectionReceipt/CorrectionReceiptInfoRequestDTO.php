<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\CorrectionReceipt;

final readonly class CorrectionReceiptInfoRequestDTO
{
    public function __construct(
        public string $receipt_id,
    ) {}
}
