<?php

declare(strict_types=1);

namespace Tests\Unit;

use DF\DigitalKassa\Enums\HttpAuthType;
use DF\DigitalKassa\Enums\HttpMethod;
use DF\DigitalKassa\Exceptions\DigitalKassaApiV21ErrorException;
use DF\DigitalKassa\Exceptions\InvalidRequestException;
use DF\DigitalKassa\Exceptions\TransportException;
use DF\DigitalKassa\Interfaces\ApiRequestInterface;
use DF\DigitalKassa\V2\DigitalKassaApi;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ItemDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptInfoRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\Shared\AmountDTO;
use DF\DigitalKassa\V2\DTO\Shared\CorrectionNotifyDTO;
use DF\DigitalKassa\V2\DTO\Shared\LocationDTO;
use DF\DigitalKassa\V2\DTO\Shared\NotifyDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftRequestDTO;
use DF\DigitalKassa\V2\Enums\InternetMode;
use DF\DigitalKassa\V2\Enums\ItemType;
use DF\DigitalKassa\V2\Enums\PaymentMethod;
use DF\DigitalKassa\V2\Enums\ReceiptType1054;
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionMethod;
use RuntimeException;
use Throwable;

final class DigitalKassaApiCoverageTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function invalidReceiptIdProvider(): array
    {
        return [
            'empty' => ['', 'cannot be empty'],
            'too long' => [str_repeat('A', 65), 'cannot be longer than 64 characters'],
        ];
    }

    /** `RequestException` без ответа должен оборачиваться в `TransportException`. */
    public function test_it_wraps_request_exception_without_response_into_transport_exception(): void
    {
        $request = new Request('GET', 'c_groups/12');
        $client = $this->makeThrowingClient(new RequestException('network', $request));
        $api = $this->makeApiWithClient($client);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('getCGroupInfo [GET c_groups/12]');

        $api->getCGroupInfo();
    }

    /** Любая непредвиденная ошибка клиента тоже должна оборачиваться в `TransportException`. */
    public function test_it_wraps_generic_exception_into_transport_exception(): void
    {
        $client = $this->makeThrowingClient(new RuntimeException('boom'));
        $api = $this->makeApiWithClient($client);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('getCGroupInfo [GET c_groups/12]');

        $api->getCGroupInfo();
    }

    /** При нормализации ошибок API элементы payload не в формате массива должны пропускаться. */
    public function test_it_skips_non_array_error_items_during_normalization(): void
    {
        $request = new Request('GET', 'c_groups/12');
        $response = new Response(400, [], json_encode([
            'bad-item',
            ['desc' => 'valid error'],
        ], JSON_THROW_ON_ERROR));
        $client = $this->makeThrowingClient(new RequestException('api error', $request, $response));
        $api = $this->makeApiWithClient($client);

        try {
            $api->getCGroupInfo();
            self::fail('Expected DigitalKassaApiV21ErrorException');
        } catch (DigitalKassaApiV21ErrorException $exception) {
            self::assertCount(1, $exception->errors);
            self::assertSame('valid error', $exception->errors[0]->desc);
            self::assertIsArray($exception->rawPayload);
        }
    }

    /** Внутренний `send()` должен добавлять query string и не отправлять пустой JSON body. */
    public function test_private_send_appends_query_and_skips_empty_serialized_body(): void
    {
        $client = new class implements ClientInterface
        {
            public string $method;

            public string $uri;

            /** @var array<string, mixed> */
            public array $options;

            /**
             * @param  array<string, mixed>  $options
             */
            public function send(RequestInterface $request, array $options = []): ResponseInterface
            {
                throw new RuntimeException('Not used');
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
            {
                throw new RuntimeException('Not used');
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function request(string $method, $uri = '', array $options = []): ResponseInterface
            {
                $this->method = $method;
                $this->uri = (string) $uri;
                $this->options = $options;

                return new Response(200, [], '{}');
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function requestAsync(string $method, $uri = '', array $options = []): PromiseInterface
            {
                return Create::promiseFor($this->request($method, $uri, $options));
            }

            public function getConfig(?string $option = null): mixed
            {
                return null;
            }
        };

        $api = $this->makeApiWithClient($client);
        $request = new class implements ApiRequestInterface
        {
            public function getUri(): string
            {
                return 'custom/endpoint';
            }

            public function getMethod(): HttpMethod
            {
                return HttpMethod::GET;
            }

            public function getAuthType(): HttpAuthType
            {
                return HttpAuthType::NONE;
            }

            public function getQuery(): string
            {
                return '?foo=bar';
            }

            public function getBody(): object
            {
                return new class
                {
                    public ?string $ignored = null;
                };
            }

            /**
             * @return array<string, string>
             */
            public function getHeaders(): array
            {
                return ['X-Test' => '1'];
            }
        };

        $method = new ReflectionMethod(DigitalKassaApi::class, 'send');
        $method->setAccessible(true);
        $method->invoke($api, 'customRequest', $request);

        self::assertSame('get', $client->method);
        self::assertSame('custom/endpoint?foo=bar', $client->uri);
        /** @var array<string, string> $headers */
        $headers = $client->options['headers'];
        self::assertSame('application/json; charset=utf-8', $headers['Content-Type']);
        self::assertSame('1', $headers['X-Test']);
        self::assertArrayNotHasKey('Authorization', $headers);
        self::assertArrayNotHasKey('json', $client->options);
    }

    private function makeApiWithClient(ClientInterface $client): DigitalKassaApi
    {
        return new DigitalKassaApi(
            credentials: new Credentials(
                actorId: 'actor',
                actorToken: 'token',
                cGroupId: 12,
            ),
            httpClient: $client,
        );
    }

    private function makeResponseClient(ResponseInterface $response): ClientInterface
    {
        return new class($response) implements ClientInterface
        {
            public function __construct(
                private ResponseInterface $response,
            ) {}

            /**
             * @param  array<string, mixed>  $options
             */
            public function send(RequestInterface $request, array $options = []): ResponseInterface
            {
                return $this->response;
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
            {
                return Create::promiseFor($this->response);
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function request(string $method, $uri = '', array $options = []): ResponseInterface
            {
                return $this->response;
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function requestAsync(string $method, $uri = '', array $options = []): PromiseInterface
            {
                return Create::promiseFor($this->response);
            }

            public function getConfig(?string $option = null): mixed
            {
                return null;
            }
        };
    }

    private function makeThrowingClient(Throwable $throwable): ClientInterface
    {
        return new class($throwable) implements ClientInterface
        {
            public function __construct(
                private Throwable $throwable,
            ) {}

            /**
             * @param  array<string, mixed>  $options
             */
            public function send(RequestInterface $request, array $options = []): ResponseInterface
            {
                throw new RuntimeException('Not used');
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
            {
                throw new RuntimeException('Not used');
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function request(string $method, $uri = '', array $options = []): ResponseInterface
            {
                throw $this->throwable;
            }

            /**
             * @param  array<string, mixed>  $options
             */
            public function requestAsync(string $method, $uri = '', array $options = []): PromiseInterface
            {
                return Create::rejectionFor($this->throwable);
            }

            public function getConfig(?string $option = null): mixed
            {
                return null;
            }
        };
    }

    private function makeReceipt(ItemDTO $item, AmountDTO $amount): ReceiptDTO
    {
        return new ReceiptDTO(
            type: ReceiptType1054::SELL,
            items: [$item],
            taxation: Taxation::OSN,
            is_internet: InternetMode::ON,
            timezone: Timezone::UTC_5,
            notify: new NotifyDTO(phone: '+79990000000'),
            amount: $amount,
            loc: new LocationDTO('site.example'),
        );
    }

    private function makeItem(
        float $price = 100.00,
        float $quantity = 1.0,
        float $amount = 100.00,
        ?float $excise = null,
    ): ItemDTO {
        return new ItemDTO(
            type: ItemType::PRODUCT,
            name: 'Coffee',
            price: $price,
            quantity: $quantity,
            amount: $amount,
            payment_method: PaymentMethod::FULL_PAYMENT,
            unit: Unit::PIECE,
            vat: VatType::VAT_20,
            excise: $excise,
        );
    }
}
