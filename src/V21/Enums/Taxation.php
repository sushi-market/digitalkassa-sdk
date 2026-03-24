<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\Enums;

enum Taxation: int
{
    case OSN = 1;
    case USN_INCOME = 2;
    case USN_INCOME_OUTCOME = 4;
    case ESN = 16;
    case PATENT = 32;
}
