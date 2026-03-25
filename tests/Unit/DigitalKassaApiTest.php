<?php

declare(strict_types=1);

namespace Tests\Unit;

use DF\DigitalKassa\Exceptions\DigitalKassaApiV21ErrorException;
use DF\DigitalKassa\Exceptions\InvalidRequestException;
use DF\DigitalKassa\V2\DigitalKassaApi;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptInfoRequestDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ItemDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptInfoRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\Shared\AmountDTO;
use DF\DigitalKassa\V2\DTO\Shared\CorrectionNotifyDTO;
use DF\DigitalKassa\V2\DTO\Shared\LocationDTO;
use DF\DigitalKassa\V2\DTO\Shared\NotifyDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftModeRequestDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftRequestDTO;
use DF\DigitalKassa\V2\Enums\InternetMode;
use DF\DigitalKassa\V2\Enums\ItemType;
use DF\DigitalKassa\V2\Enums\PaymentMethod;
use DF\DigitalKassa\V2\Enums\ProcessingStatus;
use DF\DigitalKassa\V2\Enums\ReceiptType1054;
use DF\DigitalKassa\V2\Enums\ShiftMode;
use DF\DigitalKassa\V2\Enums\ShiftStatus;
use DF\DigitalKassa\V2\Enums\Taxation;
use DF\DigitalKassa\V2\Enums\Timezone;
use DF\DigitalKassa\V2\Enums\Unit;
use DF\DigitalKassa\V2\Enums\VatType;
use DF\DigitalKassa\V2\ValueObjects\Credentials;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class DigitalKassaApiTest extends TestCase
{
    /** Обе библиотеки должны подключаться вместе без конфликта имён классов. */
    #[Test]
    public function packages_can_be_loaded_together_without_class_collision(): void
    {
        self::assertTrue(class_exists(DigitalKassaApi::class));
    }

    /** `getCGroupInfo()` должен идти в `/c_groups/{id}` и отправлять Basic Auth. */
    #[Test]
    public function get_c_group_info_uses_expected_endpoint(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], $this->json([
                'type' => 'online_store',
                'taxation' => 3,
                'billing_place_list' => ['site.example'],
            ])),
        ], $history);

        $response = $api->getCGroupInfo();

        $request = $this->assertRecordedRequest($history, 'GET', '/v2.1/c_groups/12');
        self::assertSame(
            'Basic '.base64_encode('actor:token'),
            $request->getHeaderLine('Authorization'),
        );
        self::assertSame('online_store', $response->type);
        self::assertTrue($response->supportsTaxation(Taxation::OSN));
    }

    /** `createReceipt()` должен отправлять чек в правильный endpoint и получать статус `ACCEPTED`. */
    #[Test]
    public function create_receipt_uses_expected_endpoint_and_returns_accepted_status(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(202),
        ], $history);

        $response = $api->createReceipt($this->makeReceiptRequest('RCPT001'));

        $request = $this->assertRecordedRequest($history, 'POST', '/v2.1/c_groups/12/receipts/RCPT001');
        self::assertSame(ProcessingStatus::ACCEPTED, $response->status);
        self::assertSame(
            $this->json($this->expectedReceiptPayload()),
            (string) $request->getBody(),
        );
    }

    /** `getReceiptInfo()` должен запрашивать статус чека и мапить ответ в DTO. */
    #[Test]
    public function get_receipt_info_uses_expected_endpoint_and_maps_payload(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], $this->json($this->okPayload())),
        ], $history);

        $response = $api->getReceiptInfo(new ReceiptInfoRequestDTO('RCPT002'));

        $this->assertRecordedRequest($history, 'GET', '/v2.1/c_groups/12/receipts/RCPT002');
        self::assertSame(ProcessingStatus::COMPLETED, $response->status);
        self::assertSame(15, $response->doc?->index);
        self::assertSame('https://example.com/receipt/15', $response->service?->receipt_url);
    }

    /** `createCorrectionReceipt()` должен отправлять чек коррекции в правильный endpoint. */
    #[Test]
    public function create_correction_receipt_uses_expected_endpoint_and_returns_accepted_status(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(202),
        ], $history);

        $response = $api->createCorrectionReceipt($this->makeCorrectionReceiptRequest('CORR001'));

        $request = $this->assertRecordedRequest($history, 'POST', '/v2.1/c_groups/12/receipts/correction/CORR001');
        self::assertSame(ProcessingStatus::ACCEPTED, $response->status);
        self::assertSame(
            $this->json($this->expectedCorrectionReceiptPayload()),
            (string) $request->getBody(),
        );
    }

    /** `getCorrectionReceiptInfo()` должен запрашивать статус чека коррекции и мапить ответ. */
    #[Test]
    public function get_correction_receipt_info_uses_expected_endpoint_and_maps_payload(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], $this->json($this->okPayload())),
        ], $history);

        $response = $api->getCorrectionReceiptInfo(new CorrectionReceiptInfoRequestDTO('CORR002'));

        $this->assertRecordedRequest($history, 'GET', '/v2.1/c_groups/12/receipts/correction/CORR002');
        self::assertSame(ProcessingStatus::COMPLETED, $response->status);
        self::assertSame('RN-1', $response->cashbox?->rn);
    }

    /** `getShiftReport()` должен использовать endpoint отчёта по смене и мапить его поля. */
    #[Test]
    public function get_shift_report_uses_expected_endpoint_and_maps_payload(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], $this->json([
                'shift_status' => 1,
                'shift_number' => 7,
                'check_number' => 144,
                'mode' => 0,
            ])),
        ], $history);

        $response = $api->getShiftReport();

        $this->assertRecordedRequest($history, 'GET', '/v2.1/c_groups/12/shifts/report');
        self::assertSame(ShiftStatus::OPEN, $response->shift_status);
        self::assertSame(7, $response->shift_number);
        self::assertSame(ShiftMode::AUTOMATIC, $response->mode);
    }

    /** `openShift()` должен отправлять данные кассира в endpoint открытия смены. */
    #[Test]
    public function open_shift_uses_expected_endpoint(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], $this->json([
                'shift_number' => 7,
                'fd_number' => 1001,
                'fiscal_sign' => 555555,
            ])),
        ], $history);

        $response = $api->openShift(new ShiftRequestDTO(
            name: 'Cashier',
            tin: '123456789012',
            address: 'Omsk',
            place: 'Store',
        ));

        $request = $this->assertRecordedRequest($history, 'POST', '/v2.1/c_groups/12/shifts/open');
        self::assertSame(7, $response->shift_number);
        self::assertSame($this->json([
            'name' => 'Cashier',
            'tin' => '123456789012',
            'address' => 'Omsk',
            'place' => 'Store',
        ]), (string) $request->getBody());
    }

    /** `closeShift()` должен вызывать endpoint закрытия смены без request body. */
    #[Test]
    public function close_shift_uses_expected_endpoint(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], $this->json([
                'shift_number' => 7,
                'fd_number' => 1002,
                'fiscal_sign' => 666666,
            ])),
        ], $history);

        $response = $api->closeShift();

        $request = $this->assertRecordedRequest($history, 'POST', '/v2.1/c_groups/12/shifts/close');
        self::assertSame(7, $response->shift_number);
        self::assertSame('', (string) $request->getBody());
    }

    /** `changeShiftMode()` должен отправлять выбранный режим в `/shifts/mode`. */
    #[Test]
    public function change_shift_mode_uses_expected_endpoint(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], $this->json([
                'shift_number' => 7,
                'fd_number' => 1003,
                'fiscal_sign' => 777777,
            ])),
        ], $history);

        $response = $api->changeShiftMode(new ShiftModeRequestDTO(ShiftMode::MANUAL));

        $request = $this->assertRecordedRequest($history, 'POST', '/v2.1/c_groups/12/shifts/mode');
        self::assertSame(7, $response->shift_number);
        self::assertSame($this->json(['mode' => 1]), (string) $request->getBody());
    }

    /** Ответ API с кодом `400` должен превращаться в `DigitalKassaApiV21ErrorException`. */
    #[Test]
    public function api_errors_are_normalized_from_400_responses(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(400, [], $this->json([
                [
                    'type' => 'validation',
                    'desc' => 'receipt_id invalid',
                    'path' => 'receipt_id',
                ],
            ])),
        ], $history);

        try {
            $api->getReceiptInfo(new ReceiptInfoRequestDTO('BAD001'));
            self::fail('Expected DigitalKassaApiV21ErrorException');
        } catch (DigitalKassaApiV21ErrorException $exception) {
            $this->assertRecordedRequest($history, 'GET', '/v2.1/c_groups/12/receipts/BAD001');
            self::assertSame('getReceiptInfo', $exception->sdkMethod);
            self::assertSame('GET', $exception->httpMethod);
            self::assertSame('c_groups/12/receipts/BAD001', $exception->uri);
            self::assertSame(400, $exception->statusCode);
            self::assertCount(1, $exception->errors);
            self::assertStringContainsString('receipt_id invalid', $exception->getMessage());
        }
    }

    /** Некорректный `receipt_id` должен отсеиваться до HTTP-запроса. */
    #[Test]
    public function invalid_receipt_id_is_rejected_before_http_call(): void
    {
        $history = [];
        $api = $this->makeApi([], $history);

        $this->expectException(InvalidRequestException::class);

        try {
            $api->createReceipt($this->makeReceiptRequest('bad-id'));
        } finally {
            self::assertCount(0, $history);
        }
    }

    /** Чек без контактов в `notify` не должен отправляться в API. */
    #[Test]
    public function receipt_without_notify_contact_is_rejected_before_http_call(): void
    {
        $history = [];
        $api = $this->makeApi([], $history);

        $this->expectException(InvalidRequestException::class);

        try {
            $api->createReceipt(new ReceiptRequestDTO(
                receipt_id: 'RCPT003',
                receipt: new ReceiptDTO(
                    type: ReceiptType1054::SELL,
                    items: [$this->makeItem()],
                    taxation: Taxation::OSN,
                    is_internet: InternetMode::ON,
                    timezone: Timezone::UTC_5,
                    notify: new NotifyDTO,
                    amount: new AmountDTO(cashless: 100.00),
                    loc: new LocationDTO('site.example'),
                ),
            ));
        } finally {
            self::assertCount(0, $history);
        }
    }

    /** Чек коррекции без `corrected_date` не должен уходить в API. */
    #[Test]
    public function correction_receipt_without_corrected_date_is_rejected_before_http_call(): void
    {
        $history = [];
        $api = $this->makeApi([], $history);

        $this->expectException(InvalidRequestException::class);

        try {
            $api->createCorrectionReceipt(new CorrectionReceiptRequestDTO(
                receipt_id: 'CORR003',
                correction_receipt: new CorrectionReceiptDTO(
                    type: ReceiptType1054::SELL,
                    items: [$this->makeItem()],
                    taxation: Taxation::OSN,
                    corrected_date: '',
                    amount: new AmountDTO(cashless: 100.00),
                    is_internet: InternetMode::ON,
                    timezone: Timezone::UTC_5,
                    loc: new LocationDTO('site.example'),
                    notify: new CorrectionNotifyDTO(phone: '+79990000000'),
                ),
            ));
        } finally {
            self::assertCount(0, $history);
        }
    }

    /**
     * @param  list<ResponseInterface>  $responses
     * @param  array<int, array<string, mixed>>  $history
     */
    private function makeApi(array $responses, array &$history): DigitalKassaApi
    {
        $history = [];
        $httpClient = new class($responses, $history) implements ClientInterface
        {
            /** @var list<ResponseInterface> */
            private array $responses;

            /** @var \Closure(array<string, mixed>): void */
            private \Closure $pushHistory;

            /**
             * @param  list<ResponseInterface>  $responses
             * @param  array<int, array<string, mixed>>  $history
             */
            public function __construct(array $responses, array &$history)
            {
                $this->responses = $responses;
                $this->pushHistory = static function (array $entry) use (&$history): void {
                    $history[] = $entry;
                };
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function send(RequestInterface $request, array $options = []): ResponseInterface
            {
                throw new \RuntimeException('Not used');
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
            {
                throw new \RuntimeException('Not used');
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function request(string $method, $uri = '', array $options = []): ResponseInterface
            {
                $uriString = (string) $uri;

                if (! str_starts_with($uriString, 'http')) {
                    $uriString = 'https://api.digitalkassa.ru/v2.1/'.ltrim($uriString, '/');
                }

                $headers = [];

                if (isset($options[RequestOptions::HEADERS]) && is_array($options[RequestOptions::HEADERS])) {
                    foreach ($options[RequestOptions::HEADERS] as $name => $value) {
                        if (is_string($name) && is_string($value)) {
                            $headers[$name] = $value;
                        }
                    }
                }

                $body = '';

                if (array_key_exists(RequestOptions::JSON, $options)) {
                    $body = json_encode($options[RequestOptions::JSON], JSON_THROW_ON_ERROR);
                }

                $request = new Request($method, $uriString, $headers, $body);
                $entry = [
                    'request' => $request,
                    'options' => $options,
                ];

                $response = array_shift($this->responses);

                if (! $response instanceof ResponseInterface) {
                    throw new \RuntimeException('No mock response configured');
                }

                $entry['response'] = $response;
                ($this->pushHistory)($entry);

                if (($options[RequestOptions::HTTP_ERRORS] ?? false) === true && $response->getStatusCode() >= 400) {
                    throw new RequestException('Mock request failed', $request, $response);
                }

                return $response;
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function requestAsync(string $method, $uri = '', array $options = []): PromiseInterface
            {
                try {
                    return Create::promiseFor($this->request($method, $uri, $options));
                } catch (\Throwable $exception) {
                    return Create::rejectionFor($exception);
                }
            }

            public function getConfig(?string $option = null): mixed
            {
                return null;
            }
        };

        return new DigitalKassaApi(
            credentials: new Credentials(
                actorId: 'actor',
                actorToken: 'token',
                cGroupId: 12,
            ),
            httpClient: $httpClient,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $history
     */
    private function assertRecordedRequest(array $history, string $method, string $path): RequestInterface
    {
        self::assertCount(1, $history);

        /** @var RequestInterface $request */
        $request = $history[0]['request'];

        self::assertSame($method, $request->getMethod());
        self::assertSame($path, $request->getUri()->getPath());
        self::assertSame('application/json; charset=utf-8', $request->getHeaderLine('Content-Type'));

        return $request;
    }

    private function makeReceiptRequest(string $receiptId): ReceiptRequestDTO
    {
        return new ReceiptRequestDTO(
            receipt_id: $receiptId,
            receipt: new ReceiptDTO(
                type: ReceiptType1054::SELL,
                items: [$this->makeItem()],
                taxation: Taxation::OSN,
                is_internet: InternetMode::ON,
                timezone: Timezone::UTC_5,
                notify: new NotifyDTO(
                    emails: ['customer@example.com'],
                    phone: '+79990000000',
                ),
                amount: new AmountDTO(cashless: 100.00),
                loc: new LocationDTO(
                    billing_place: 'site.example',
                    device_number: 'device-1',
                ),
            ),
        );
    }

    private function makeCorrectionReceiptRequest(string $receiptId): CorrectionReceiptRequestDTO
    {
        return new CorrectionReceiptRequestDTO(
            receipt_id: $receiptId,
            correction_receipt: new CorrectionReceiptDTO(
                type: ReceiptType1054::SELL_REFUND,
                items: [$this->makeItem()],
                taxation: Taxation::OSN,
                corrected_date: '24.03.2026',
                amount: new AmountDTO(cashless: 100.00),
                is_internet: InternetMode::ON,
                timezone: Timezone::UTC_5,
                loc: new LocationDTO(
                    billing_place: 'site.example',
                    device_number: 'device-2',
                ),
                notify: new CorrectionNotifyDTO(phone: '+79990000000'),
                order_number: 'ORD-1',
            ),
        );
    }

    private function makeItem(): ItemDTO
    {
        return new ItemDTO(
            type: ItemType::PRODUCT,
            name: 'Coffee',
            price: 100.00,
            quantity: 1.0,
            amount: 100.00,
            payment_method: PaymentMethod::FULL_PAYMENT,
            unit: Unit::PIECE,
            vat: VatType::VAT_20,
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    private function expectedReceiptPayload(): array
    {
        return [
            'type' => 1,
            'items' => [[
                'type' => 1,
                'name' => 'Coffee',
                'price' => 100.0,
                'quantity' => 1.0,
                'amount' => 100.0,
                'payment_method' => 4,
                'unit' => 0,
                'vat' => 1,
            ]],
            'taxation' => 1,
            'is_internet' => 1,
            'timezone' => 4,
            'notify' => [
                'emails' => ['customer@example.com'],
                'phone' => '+79990000000',
            ],
            'amount' => [
                'cashless' => 100.0,
            ],
            'loc' => [
                'billing_place' => 'site.example',
                'device_number' => 'device-1',
            ],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    private function expectedCorrectionReceiptPayload(): array
    {
        return [
            'type' => 2,
            'items' => [[
                'type' => 1,
                'name' => 'Coffee',
                'price' => 100.0,
                'quantity' => 1.0,
                'amount' => 100.0,
                'payment_method' => 4,
                'unit' => 0,
                'vat' => 1,
            ]],
            'taxation' => 1,
            'corrected_date' => '24.03.2026',
            'amount' => [
                'cashless' => 100.0,
            ],
            'is_internet' => 1,
            'timezone' => 4,
            'loc' => [
                'billing_place' => 'site.example',
                'device_number' => 'device-2',
            ],
            'order_number' => 'ORD-1',
            'notify' => [
                'phone' => '+79990000000',
            ],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    private function okPayload(): array
    {
        return [
            'doc' => [
                'reg_time' => '2026-03-24T12:00:00+06:00',
                'shift_num' => 3,
                'index' => 15,
                'fiscal_sign' => 123456,
                'fiscal_num' => 321654,
            ],
            'cashbox' => [
                'rn' => 'RN-1',
                'factory_num' => 'FACT-1',
                'ffd' => '1.2',
                'fn_num' => 'FN-1',
            ],
            'service' => [
                'callback_url' => 'https://example.com/callback',
                'receipt_url' => 'https://example.com/receipt/15',
            ],
        ];
    }

    /**
     * @param  array<array-key, mixed>  $payload
     */
    private function json(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR);
    }
}
