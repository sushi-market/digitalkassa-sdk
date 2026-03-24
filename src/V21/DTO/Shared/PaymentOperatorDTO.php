<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

final readonly class PaymentOperatorDTO
{
    /** @param string[] $phones */
    public function __construct(
        public array $phones,
    ) {}
}
