<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

final readonly class CashlessPaymentsDTO
{
    public function __construct(
        public ?float $payment_sum = null,
        public ?float $payment_method_flag = null,
        public ?string $payment_identifiers = null,
        public ?string $additional_info = null,
    ) {}
}
