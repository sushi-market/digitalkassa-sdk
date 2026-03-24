<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

use DF\DigitalKassa\V21\Enums\AgentType;

final readonly class BankPayingAgentDTO implements AgentDTO
{
    public function __construct(
        public SupplierDTO $supplier,
        public BankPayingAgentDetailsDTO $paying_agent,
        public MoneyTransferOperatorDTO $money_transfer_operator,
        public AgentType $type = AgentType::BANK_PAYING_AGENT,
    ) {}
}
