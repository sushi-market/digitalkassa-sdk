<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

final readonly class OkShiftStatusPayloadDTO
{
    public function __construct(
        public ?int $shift_status = null,
        public ?int $shift_number = null,
        public ?int $check_number = null,
        public ?int $mode = null,
    ) {}
}
