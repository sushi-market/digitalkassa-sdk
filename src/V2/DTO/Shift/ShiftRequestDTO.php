<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shift;

final readonly class ShiftRequestDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $tin = null,
        public ?string $address = null,
        public ?string $place = null,
    ) {}
}
