<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

final readonly class CustomerDTO
{
    public function __construct(
        public ?string $tin = null,
        public ?string $name = null,
    ) {}
}
