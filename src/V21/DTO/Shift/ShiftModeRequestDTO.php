<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shift;

use DF\DigitalKassa\V21\Enums\ShiftMode;

final readonly class ShiftModeRequestDTO
{
    public function __construct(
        public ShiftMode $mode,
    ) {}
}
