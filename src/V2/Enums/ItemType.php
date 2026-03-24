<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Enums;

enum ItemType: int
{
    case PRODUCT = 1;
    case EXCISE_PRODUCT = 2;
    case WORK = 3;
    case SERVICE = 4;
    case GAMBLING_BET = 5;
    case GAMBLING_PRIZE = 6;
    case LOTTERY = 7;
    case LOTTERY_PRIZE = 8;
    case INTELLECTUAL_PROPERTY = 9;
    case ADVANCE = 10;
    case AGENT_COMMISSION = 11;
    case PAYMENT = 12;
    case OTHER = 13;
    case PROPERTY_RIGHT = 14;
    case NON_OPERATING_INCOME = 15;
    case TAX_REDUCTION = 16;
    case TRADE_FEE = 17;
    case RESORT_FEE = 18;
    case DEPOSIT = 19;
    case EXPENSE = 20;
    case PENSION_INSURANCE_IP = 21;
    case PENSION_INSURANCE_ORGANIZATION = 22;
    case MEDICAL_INSURANCE_IP = 23;
    case MEDICAL_INSURANCE_ORGANIZATION = 24;
    case SOCIAL_INSURANCE = 25;
    case CASINO_CHIPS = 26;
    case CASH_WITHDRAWAL = 27;
    case EXCISE_PRODUCT_UNMARKED = 30;
    case EXCISE_PRODUCT_MARKED = 31;
    case PRODUCT_UNMARKED = 32;
    case PRODUCT_MARKED = 33;
}
