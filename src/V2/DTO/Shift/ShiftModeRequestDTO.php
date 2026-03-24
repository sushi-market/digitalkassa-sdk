<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shift;

use DF\DigitalKassa\V2\Enums\ShiftMode;

final readonly class ShiftModeRequestDTO
{
    public function __construct(
        public ShiftMode $mode,
    ) {}
}
