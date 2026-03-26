<?php

declare(strict_types=1);

namespace Tests\Feature\V2;

use DF\DigitalKassa\Exceptions\DigitalKassaApiV2ErrorException;
use DF\DigitalKassa\V2\DigitalKassaApi;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptInfoRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptInfoResponseDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptItemDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptResponseDTO;
use DF\DigitalKassa\V2\DTO\Shared\AmountDTO;
use DF\DigitalKassa\V2\DTO\Shared\LocationDTO;
use DF\DigitalKassa\V2\DTO\Shared\NotifyDTO;
use DF\DigitalKassa\V2\Enums\InternetMode;
use DF\DigitalKassa\V2\Enums\PaymentMethod;
use DF\DigitalKassa\V2\Enums\ReceiptItemType;
use DF\DigitalKassa\V2\Enums\ReceiptType;
use DF\DigitalKassa\V2\Enums\Taxation;
use DF\DigitalKassa\V2\Enums\Timezone;
use DF\DigitalKassa\V2\Enums\Unit;
use DF\DigitalKassa\V2\Enums\VatType;
use DF\DigitalKassa\V2\ValueObjects\Credentials;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateSellReceiptTest extends TestCase
{
    private ClientInterface&MockObject $httpClient;

    private DigitalKassaApi $api;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);

        $credentials = new Credentials(
            actorId: 'test-login',
            actorToken: 'test-password',
            cGroupId: 123,
        );

        $this->api = new DigitalKassaApi(
            credentials: $credentials,
            httpClient: $this->httpClient,
        );
    }

    /**
     * Happy path: API вернул валидный JSON — метод возвращает заполненный DTO.
     */
    public function test_create_sell_receipt_returns_mapped_dto(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn(new Response(
                status: 200,
                body: json_encode([
                    'doc' => [
                        'reg_time' => '2019-01-01T12:00:00',
                        'shift_num' => 1,
                        'index' => 1,
                        'fiscal_sign' => 2,
                        'fiscal_num' => 1111111111,
                    ],

                    'cashbox' => [
                        'rn' => '000444444444444',
                        'ffd' => '4',
                        'fn_num' => '9999999999999999',
                        'factory_num' => '555555555555',
                    ],

                    'service' => [
                        'receipt_url' => 'https://example.com/d5djQyNncxbFx1MDAxZDkxRkZEMFx1MDAxZDk',
                    ],
                ]),
            ));

        $result = $this->api->createReceipt(
            requestDTO: new ReceiptRequestDTO(
                receipt_id: 'test10',
                receipt: new ReceiptDTO(
                    type: ReceiptType::SELL,

                    items: [
                        new ReceiptItemDTO(
                            type: ReceiptItemType::PRODUCT,
                            name: 'Тестовый товар',
                            price: 1.0,
                            quantity: 2.0,
                            amount: 2.0,
                            payment_method: PaymentMethod::FULL_PREPAYMENT,
                            unit: Unit::PIECE,
                            vat: VatType::VAT_0,
                        ),
                    ],

                    taxation: Taxation::USN_INCOME,
                    is_internet: InternetMode::ON,
                    timezone: Timezone::UTC_6,

                    notify: new NotifyDTO(
                        emails: [
                            'test@example.com',
                        ],

                        phone: '+79999999999',
                    ),

                    amount: new AmountDTO(
                        cash: 0.0,
                        cashless: 2.0,
                        prepayment: 0.0,
                        postpayment: 0.0,
                        barter: 0.0,
                    ),

                    loc: new LocationDTO(
                        billing_place: 'https://example.com',
                    ),
                ),
            ),
        );

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ReceiptResponseDTO::class, $result);
    }

    /**
     * API вернул ошибку BAD_VALUE
     */
    public function test_get_receipt_info_bad_value(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(
                new RequestException(
                    message: 'Bad Request',
                    request: new Request(
                        method: 'GET',
                        uri: 'mock',
                    ),
                    response: new Response(
                        status: 400,
                        body: json_encode([
                            [
                                'type' => 'BAD_VALUE',
                                'desc' => 'Ожидается тип `number`',
                                'path' => '$.amount.barter',
                            ],
                        ]),
                    ),
                ));

        $this->expectException(DigitalKassaApiV2ErrorException::class);

        $this->api->openShift();
    }
}
