<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Enums;

enum ShiftStatus: int
{
    case CLOSED = 0;
    case OPEN = 1;
}
