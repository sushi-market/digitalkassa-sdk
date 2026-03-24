<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\Enums;

enum MarkingItemStatus: int
{
    case SOLD_UNIT = 1;
    case PARTIAL_SALE = 2;
    case RETURNED_UNIT = 3;
    case RETURNED_PARTIAL = 4;
    case UNCHANGED = 255;
}
