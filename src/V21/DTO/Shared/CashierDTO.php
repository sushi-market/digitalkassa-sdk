<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

final readonly class CashierDTO
{
    public function __construct(
        public string $name,
        public ?string $tin = null,
    ) {}
}
