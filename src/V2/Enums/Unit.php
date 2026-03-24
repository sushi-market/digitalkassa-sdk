<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Enums;

enum Unit: int
{
    case PIECE = 0;
    case GRAM = 10;
    case KILOGRAM = 11;
    case TON = 12;
    case CENTIMETER = 20;
    case DECIMETER = 21;
    case METER = 22;
    case SQUARE_CENTIMETER = 30;
    case SQUARE_DECIMETER = 31;
    case SQUARE_METER = 32;
    case MILLILITER = 40;
    case LITER = 41;
    case CUBIC_METER = 42;
    case KILOWATT_HOUR = 50;
    case GIGACALORIE = 51;
    case DAY = 70;
    case HOUR = 71;
    case MINUTE = 72;
    case SECOND = 73;
    case KILOBYTE = 80;
    case MEGABYTE = 81;
    case GIGABYTE = 82;
    case TERABYTE = 83;
    case OTHER = 255;
}
