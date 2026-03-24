<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

use DF\DigitalKassa\V2\Enums\MarkingItemStatus;

final readonly class MarkingDTO
{
    public function __construct(
        public string $code,
        public MarkingItemStatus $item_status,
        public ?int $numerator = null,
        public ?int $denominator = null,
    ) {}
}
