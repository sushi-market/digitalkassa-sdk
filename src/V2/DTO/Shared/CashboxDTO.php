<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

final readonly class CashboxDTO
{
    public function __construct(
        public ?string $rn = null,
        public ?string $factory_num = null,
        public ?string $ffd = null,
        public ?string $fn_num = null,
    ) {}
}
