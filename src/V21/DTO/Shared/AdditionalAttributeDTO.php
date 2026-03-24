<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

final readonly class AdditionalAttributeDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $value = null,
    ) {}
}
