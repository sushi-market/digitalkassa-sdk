<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\Enums;

enum ReceiptType1054: int
{
    case SELL = 1;
    case SELL_REFUND = 2;
    case BUY = 3;
    case BUY_REFUND = 4;
}
