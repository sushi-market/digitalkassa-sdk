<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

use DF\DigitalKassa\V2\Enums\AgentType;

final readonly class CommissionAgentDTO implements AgentDTO
{
    public function __construct(
        public SupplierDTO $supplier,
        public AgentType $type = AgentType::COMMISSION_AGENT,
    ) {}
}
