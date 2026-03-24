<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\CGroup;

use DF\DigitalKassa\V2\Enums\Taxation;

final readonly class CGroupInfoResponseDTO
{
    /** @param string[] $billing_place_list */
    public function __construct(
        public string $type,
        public int $taxation,
        public array $billing_place_list,
    ) {}

    public function supportsTaxation(Taxation $taxation): bool
    {
        return ($this->taxation & $taxation->value) === $taxation->value;
    }
}
