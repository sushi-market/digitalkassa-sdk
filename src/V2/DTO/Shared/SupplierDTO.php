<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

final readonly class SupplierDTO
{
    /** @param string[] $phones */
    public function __construct(
        public array $phones,
        public string $name,
        public string $tin,
    ) {}
}
