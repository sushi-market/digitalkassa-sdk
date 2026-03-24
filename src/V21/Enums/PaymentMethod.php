<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\Enums;

enum PaymentMethod: int
{
    case FULL_PREPAYMENT = 1;
    case PREPAYMENT = 2;
    case ADVANCE = 3;
    case FULL_PAYMENT = 4;
    case PARTIAL_PAYMENT_AND_CREDIT = 5;
    case CREDIT = 6;
    case CREDIT_PAYMENT = 7;
}
