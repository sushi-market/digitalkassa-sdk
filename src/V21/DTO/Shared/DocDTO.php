<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

final readonly class DocDTO
{
    public function __construct(
        public ?string $reg_time = null,
        public ?int $shift_num = null,
        public ?int $index = null,
        public ?int $fiscal_sign = null,
        public ?int $fiscal_num = null,
    ) {}
}
