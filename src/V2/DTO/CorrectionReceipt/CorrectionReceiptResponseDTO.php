<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\CorrectionReceipt;

use DF\DigitalKassa\V2\DTO\Shared\CashboxDTO;
use DF\DigitalKassa\V2\DTO\Shared\DocDTO;
use DF\DigitalKassa\V2\DTO\Shared\ReceiptServiceInfoDTO;

final readonly class CorrectionReceiptResponseDTO
{
    public function __construct(
        public DocDTO $doc,
        public CashboxDTO $cashbox,
        public ReceiptServiceInfoDTO $service,
    ) {}
}
