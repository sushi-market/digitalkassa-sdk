<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

final readonly class OkShiftPayloadDTO
{
    public function __construct(
        public ?int $shift_number = null,
        public ?int $fd_number = null,
        public ?int $fiscal_sign = null,
    ) {}
}
