<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Receipt;

final readonly class ReceiptInfoRequestDTO
{
    public function __construct(
        public string $receipt_id,
    ) {}
}
