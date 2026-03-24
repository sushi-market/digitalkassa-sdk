<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Receipt;

use DF\DigitalKassa\V21\DTO\Shared\CashboxDTO;
use DF\DigitalKassa\V21\DTO\Shared\DocDTO;
use DF\DigitalKassa\V21\DTO\Shared\ReceiptServiceInfoDTO;
use DF\DigitalKassa\V21\Enums\ProcessingStatus;

final readonly class ReceiptInfoResponseDTO
{
    public function __construct(
        public ProcessingStatus $status,
        public ?DocDTO $doc = null,
        public ?CashboxDTO $cashbox = null,
        public ?ReceiptServiceInfoDTO $service = null,
    ) {}
}
