<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

use DF\DigitalKassa\V21\Enums\AgentType;

final readonly class AttorneyDTO implements AgentDTO
{
    public function __construct(
        public SupplierDTO $supplier,
        public AgentType $type = AgentType::ATTORNEY,
    ) {}
}
