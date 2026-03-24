<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

final readonly class OkPayloadDTO
{
    public function __construct(
        public ?DocDTO $doc = null,
        public ?CashboxDTO $cashbox = null,
        public ?ReceiptServiceInfoDTO $service = null,
    ) {}
}
