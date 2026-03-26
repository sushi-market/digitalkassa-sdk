<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shift;

final readonly class ShiftResponseDTO
{
    public function __construct(
        public int $shift_number,
        public int $fd_number,
        public int $fiscal_sign,
    ) {}
}
