<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

final readonly class ErrorDTO
{
    public function __construct(
        public ?string $type,
        public string $desc,
        public ?string $path = null,
    ) {}
}
