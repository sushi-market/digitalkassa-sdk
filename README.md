# DigitalKassa SDK for PHP

[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-blue.svg)](https://php.net)
[![Latest Version](https://img.shields.io/github/release/sushi-market/digitalkassa-sdk.svg?style=flat-square)](https://github.com/sushi-market/digitalkassa-sdk/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/sushi-market/digitalkassa-sdk.svg?style=flat-square)](https://packagist.org/packages/sushi-market/digitalkassa-sdk)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

SDK на основе версии документа **2.1.0**

Установка:

```bash
composer require sushi-market/digitalkassa-sdk
```

```php
use DF\DigitalKassa\V2\DigitalKassaApi;
use DF\DigitalKassa\V2\ValueObjects\Credentials;

$digitalkassa = new DigitalKassaApi(
    credentials: new Credentials(
        actorId: '1234567',
        actorToken: 'secret-token',
        cGroupId: 1,
    ),
);
```

## 💰 Работа с НДС (VatType)

В SDK доступен enum `VatType`, который инкапсулирует тип ставки НДС для чеков DigitalKassa и содержит хелперы для расчёта суммы с НДС, выделения НДС и получения суммы без НДС.

Поддерживаются:
- обычные ставки (0%, 5%, 7%, 10%, 20%, 22%)
- расчетные ставки (5/105, 7/107, 10/110, 20/120, 22/122)
- режим без НДС

### Начисление НДС (из цены без НДС в цену с НДС)

```php
use DF\DigitalKassa\V2\Enums\VatType;

$vat = VatType::VAT_20;

$net = 1000.00;
$gross = $vat->applyVat($net);

// 1200.00
```

### Выделение НДС из суммы с НДС

```php
use DF\DigitalKassa\V2\Enums\VatType;

$vat = VatType::VAT_20;

$gross = 1200.00;
$vatAmount = $vat->extractVat($gross);

// 200.00
```

### Получение суммы без НДС

```php
use DF\DigitalKassa\V2\Enums\VatType;

$vat = VatType::VAT_20;

$gross = 1200.00;
$net = $vat->removeVat($gross);

// 1000.00
```

## Основные методы

- `getCGroupInfo()`
- `createReceipt()`
- `getReceiptInfo()`
- `createCorrectionReceipt()`
- `getCorrectionReceiptInfo()`
- `getShiftReport()`
- `openShift()`
- `closeShift()`
- `changeShiftMode()`

## Быстрый пример создания чека

```php
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptItemDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\Shared\AmountDTO;
use DF\DigitalKassa\V2\DTO\Shared\LocationDTO;
use DF\DigitalKassa\V2\DTO\Shared\NotifyDTO;
use DF\DigitalKassa\V2\Enums\InternetMode;
use DF\DigitalKassa\V2\Enums\ReceiptItemType;
use DF\DigitalKassa\V2\Enums\PaymentMethod;
use DF\DigitalKassa\V2\Enums\ReceiptType;
use DF\DigitalKassa\V2\Enums\Taxation;
use DF\DigitalKassa\V2\Enums\Timezone;
use DF\DigitalKassa\V2\Enums\Unit;
use DF\DigitalKassa\V2\Enums\VatType;

$response = $digitalkassa->createReceipt(new ReceiptRequestDTO(
    receipt_id: 'receipt123',
    receipt: new ReceiptDTO(
        type: ReceiptType::SELL,
        items: [
            new ReceiptItemDTO(
                type: ReceiptItemType::PRODUCT,
                name: 'Coffee',
                price: 100.00,
                quantity: 1.0,
                amount: 100.00,
                payment_method: PaymentMethod::FULL_PAYMENT,
                unit: Unit::PIECE,
                vat: VatType::VAT_20,
            ),
        ],
        taxation: Taxation::OSN,
        is_internet: InternetMode::ON,
        timezone: Timezone::UTC_5,
        notify: new NotifyDTO(
            emails: ['customer@example.com'],
            phone: '+79990000000',
        ),
        amount: new AmountDTO(cashless: 100.00),
        loc: new LocationDTO(billing_place: 'site.example'),
    ),
));
```

## Примеры вызова

### Получение информации о группе касс

```php
$cGroupInfo = $digitalkassa->getCGroupInfo();
```

### Получение статуса чека

```php
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptInfoRequestDTO;

$receiptInfo = $digitalkassa->getReceiptInfo(
    new ReceiptInfoRequestDTO(receipt_id: 'receipt123'),
);
```

### Создание чека коррекции

```php
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\Shared\CorrectionNotifyDTO;

$correctionResponse = $digitalkassa->createCorrectionReceipt(new CorrectionReceiptRequestDTO(
    receipt_id: 'correction123',
    correction_receipt: new CorrectionReceiptDTO(
        type: ReceiptType1054::SELL_REFUND,
        items: [
            new ItemDTO(
                type: ItemType::PRODUCT,
                name: 'Coffee',
                price: 100.00,
                quantity: 1.0,
                amount: 100.00,
                payment_method: PaymentMethod::FULL_PAYMENT,
                unit: Unit::PIECE,
                vat: VatType::VAT_20,
            ),
        ],
        taxation: Taxation::OSN,
        corrected_date: '24.03.2026',
        amount: new AmountDTO(cashless: 100.00),
        is_internet: InternetMode::ON,
        timezone: Timezone::UTC_5,
        loc: new LocationDTO(billing_place: 'site.example'),
        notify: new CorrectionNotifyDTO(phone: '+79990000000'),
    ),
));
```

### Получение статуса чека коррекции

```php
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptInfoRequestDTO;

$correctionInfo = $digitalkassa->getCorrectionReceiptInfo(
    new CorrectionReceiptInfoRequestDTO(receipt_id: 'correction123'),
);
```

### Работа со сменой

```php
use DF\DigitalKassa\V2\DTO\Shift\ShiftModeRequestDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftRequestDTO;
use DF\DigitalKassa\V2\Enums\ShiftMode;

$shiftReport = $digitalkassa->getShiftReport();

$digitalkassa->openShift(new ShiftRequestDTO(
    name: 'Cashier',
    tin: '123456789012',
));

$digitalkassa->changeShiftMode(new ShiftModeRequestDTO(
    mode: ShiftMode::MANUAL,
));

$digitalkassa->closeShift();
```

## Документация

- [DigitalKassa API v2.1](https://api.digitalkassa.ru/v2.1/doc)
- [OpenAPI 2.1.0](https://api.digitalkassa.ru/v2.1/APIv2.1.json?type=api&format=json&dereference=true)
