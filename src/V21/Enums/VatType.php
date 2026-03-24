<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\Enums;

enum VatType: int
{
    case VAT_20 = 1;
    case VAT_10 = 2;
    case VAT_120 = 3;
    case VAT_110 = 4;
    case VAT_0 = 5;
    case NONE = 6;
    case VAT_5 = 7;
    case VAT_7 = 8;
    case VAT_105 = 9;
    case VAT_107 = 10;
    case VAT_22 = 11;
    case VAT_122 = 12;

    public function percent(): float
    {
        return match ($this) {
            self::VAT_0, self::NONE => 0.0,
            self::VAT_5, self::VAT_105 => 5.0,
            self::VAT_7, self::VAT_107 => 7.0,
            self::VAT_10, self::VAT_110 => 10.0,
            self::VAT_20, self::VAT_120 => 20.0,
            self::VAT_22, self::VAT_122 => 22.0,
        };
    }

    public function applyVat(float $amount): float
    {
        $vat = $amount * ($this->percent() / 100);

        return round(
            num: $amount + $vat,
            precision: 2,
        );
    }

    public function extractVat(float $amountWithVat): float
    {
        $vat = $amountWithVat * ($this->percent() / (100 + $this->percent()));

        return round($vat, 2);
    }

    public function removeVat(float $amountWithVat): float
    {
        $vat = $this->extractVat($amountWithVat);

        return round($amountWithVat - $vat, 2);
    }
}
