<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

use DF\DigitalKassa\V2\Enums\AgentType;

final readonly class PayingSubagentDTO implements AgentDTO
{
    public function __construct(
        public SupplierDTO $supplier,
        public PayingAgentDetailsDTO $paying_agent,
        public PaymentOperatorDTO $payment_operator,
        public AgentType $type = AgentType::PAYING_SUBAGENT,
    ) {}
}
