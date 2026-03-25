<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shift;

use DF\DigitalKassa\V2\Enums\ShiftMode;
use DF\DigitalKassa\V2\Enums\ShiftStatus;

final readonly class ShiftReportResponseDTO
{
    public function __construct(
        public ShiftStatus $shift_status,
        public int $shift_number,
        public int $check_number,
        public ShiftMode $mode,
    ) {}
}
