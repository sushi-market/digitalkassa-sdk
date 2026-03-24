<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Receipt;

use DF\DigitalKassa\V21\DTO\Shared\AdditionalAttributeDTO;
use DF\DigitalKassa\V21\DTO\Shared\AmountDTO;
use DF\DigitalKassa\V21\DTO\Shared\CashierDTO;
use DF\DigitalKassa\V21\DTO\Shared\CashlessPaymentsDTO;
use DF\DigitalKassa\V21\DTO\Shared\CustomerDTO;
use DF\DigitalKassa\V21\DTO\Shared\LocationDTO;
use DF\DigitalKassa\V21\DTO\Shared\NotifyDTO;
use DF\DigitalKassa\V21\DTO\Shared\ServiceDTO;
use DF\DigitalKassa\V21\Enums\InternetMode;
use DF\DigitalKassa\V21\Enums\ReceiptType1054;
use DF\DigitalKassa\V21\Enums\Taxation;
use DF\DigitalKassa\V21\Enums\Timezone;

final readonly class ReceiptDTO
{
    /** @param ItemDTO[] $items */
    public function __construct(
        public ReceiptType1054 $type,
        public array $items,
        public Taxation $taxation,
        public InternetMode $is_internet,
        public Timezone $timezone,
        public NotifyDTO $notify,
        public AmountDTO $amount,
        public LocationDTO $loc,
        public ?CashlessPaymentsDTO $cashless_payments = null,
        public ?CustomerDTO $customer = null,
        public ?AdditionalAttributeDTO $additional_attribute = null,
        public ?CashierDTO $cashier = null,
        public ?ServiceDTO $service = null,
    ) {}
}
