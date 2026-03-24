<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Enums;

enum ShiftMode: int
{
    case AUTOMATIC = 0;
    case MANUAL = 1;
}
