<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Enums;

enum ReceiptType: int
{
    case SELL = 1;
    case SELL_REFUND = 2;
    case BUY = 3;
    case BUY_REFUND = 4;
}
