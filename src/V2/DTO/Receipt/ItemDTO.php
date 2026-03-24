<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Receipt;

use DF\DigitalKassa\V2\DTO\Shared\AgentDTO;
use DF\DigitalKassa\V2\DTO\Shared\MarkingDTO;
use DF\DigitalKassa\V2\Enums\ItemType;
use DF\DigitalKassa\V2\Enums\PaymentMethod;
use DF\DigitalKassa\V2\Enums\Unit;
use DF\DigitalKassa\V2\Enums\VatType;

final readonly class ItemDTO
{
    public function __construct(
        public ItemType $type,
        public string $name,
        public float $price,
        public float $quantity,
        public float $amount,
        public PaymentMethod $payment_method,
        public Unit $unit,
        public VatType $vat,
        public ?float $excise = null,
        public ?string $barcode = null,
        public ?string $additional_props = null,
        public ?string $declaration_number = null,
        public ?string $country_code = null,
        public ?MarkingDTO $marking = null,
        public ?AgentDTO $agent = null,
    ) {}
}
