<?php

declare(strict_types=1);

namespace Tests\Unit;

use DF\DigitalKassa\V2\Enums\VatType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VatTypeTest extends TestCase
{
    /** Каждый кейс `VatType` должен возвращать свою ставку НДС. */
    #[Test]
    public function it_returns_expected_percent_for_each_vat_type(): void
    {
        self::assertSame(20.0, VatType::VAT_20->percent());
        self::assertSame(10.0, VatType::VAT_10->percent());
        self::assertSame(20.0, VatType::VAT_120->percent());
        self::assertSame(10.0, VatType::VAT_110->percent());
        self::assertSame(0.0, VatType::VAT_0->percent());
        self::assertSame(0.0, VatType::NONE->percent());
        self::assertSame(5.0, VatType::VAT_5->percent());
        self::assertSame(7.0, VatType::VAT_7->percent());
        self::assertSame(5.0, VatType::VAT_105->percent());
        self::assertSame(7.0, VatType::VAT_107->percent());
        self::assertSame(22.0, VatType::VAT_22->percent());
        self::assertSame(22.0, VatType::VAT_122->percent());
    }

    /** `applyVat()` должен добавлять НДС к исходной сумме. */
    #[Test]
    public function it_applies_vat_to_amount(): void
    {
        self::assertSame(1200.0, VatType::VAT_20->applyVat(1000.0));
        self::assertSame(1050.0, VatType::VAT_5->applyVat(1000.0));
        self::assertSame(1000.0, VatType::NONE->applyVat(1000.0));
    }

    /** `extractVat()` должен выделять сумму НДС из итоговой суммы. */
    #[Test]
    public function it_extracts_vat_from_amount(): void
    {
        self::assertSame(200.0, VatType::VAT_20->extractVat(1200.0));
        self::assertSame(200.0, VatType::VAT_120->extractVat(1200.0));
        self::assertSame(0.0, VatType::NONE->extractVat(1200.0));
    }

    /** `removeVat()` должен возвращать сумму без НДС. */
    #[Test]
    public function it_removes_vat_from_amount(): void
    {
        self::assertSame(1000.0, VatType::VAT_20->removeVat(1200.0));
        self::assertSame(1000.0, VatType::VAT_120->removeVat(1200.0));
        self::assertSame(1200.0, VatType::NONE->removeVat(1200.0));
    }
}
