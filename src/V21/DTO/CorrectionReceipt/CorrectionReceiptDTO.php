<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\CorrectionReceipt;

use DF\DigitalKassa\V21\DTO\Receipt\ItemDTO;
use DF\DigitalKassa\V21\DTO\Shared\AdditionalAttributeDTO;
use DF\DigitalKassa\V21\DTO\Shared\AmountDTO;
use DF\DigitalKassa\V21\DTO\Shared\CashierDTO;
use DF\DigitalKassa\V21\DTO\Shared\CorrectionNotifyDTO;
use DF\DigitalKassa\V21\DTO\Shared\CustomerDTO;
use DF\DigitalKassa\V21\DTO\Shared\LocationDTO;
use DF\DigitalKassa\V21\DTO\Shared\ServiceDTO;
use DF\DigitalKassa\V21\Enums\InternetMode;
use DF\DigitalKassa\V21\Enums\ReceiptType1054;
use DF\DigitalKassa\V21\Enums\Taxation;
use DF\DigitalKassa\V21\Enums\Timezone;

final readonly class CorrectionReceiptDTO
{
    /** @param ItemDTO[] $items */
    public function __construct(
        public ReceiptType1054 $type,
        public array $items,
        public Taxation $taxation,
        public string $corrected_date,
        public AmountDTO $amount,
        public InternetMode $is_internet,
        public Timezone $timezone,
        public LocationDTO $loc,
        public ?string $order_number = null,
        public ?CorrectionNotifyDTO $notify = null,
        public ?CustomerDTO $customer = null,
        public ?AdditionalAttributeDTO $additional_attribute = null,
        public ?CashierDTO $cashier = null,
        public ?ServiceDTO $service = null,
    ) {}
}
