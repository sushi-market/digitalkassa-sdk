<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\Enums;

enum AgentType: int
{
    case BANK_PAYING_AGENT = 1;
    case BANK_PAYING_SUBAGENT = 2;
    case PAYING_AGENT = 4;
    case PAYING_SUBAGENT = 8;
    case ATTORNEY = 16;
    case COMMISSION_AGENT = 32;
    case ANOTHER = 64;
}
