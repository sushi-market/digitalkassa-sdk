<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

final readonly class AmountDTO
{
    public function __construct(
        public ?float $cash = null,
        public ?float $cashless = null,
        public ?float $prepayment = null,
        public ?float $postpayment = null,
        public ?float $barter = null,
    ) {}
}
