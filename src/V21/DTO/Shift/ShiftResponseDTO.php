<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shift;

final readonly class ShiftResponseDTO
{
    public function __construct(
        public ?int $shift_number = null,
        public ?int $fd_number = null,
        public ?int $fiscal_sign = null,
    ) {}
}
