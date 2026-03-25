<?php

declare(strict_types=1);

namespace Tests\Feature\V2;

use DF\DigitalKassa\Exceptions\DigitalKassaApiV2ErrorException;
use DF\DigitalKassa\V2\DigitalKassaApi;
use DF\DigitalKassa\V2\DTO\Shift\ShiftReportResponseDTO;
use DF\DigitalKassa\V2\ValueObjects\Credentials;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetShiftReportTest extends TestCase
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
    public function test_get_shift_report_returns_mapped_dto(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn(new Response(
                status: 200,
                body: json_encode([
                    'shift_status' => 0,
                    'shift_number' => 73,
                    'check_number' => 0,
                    'mode' => 0,
                ]),
            ));

        $result = $this->api->getShiftReport();

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(ShiftReportResponseDTO::class, $result);
    }

    /**
     * API вернул ошибку ERR_TIMED_OUT
     */
    public function test_get_shift_report_timeout(): void
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
                                'type' => 'ERR_TIMED_OUT',
                                'desc' => 'Превышение времени ожидания ответа',
                            ],
                        ]),
                    ),
                ));

        $this->expectException(DigitalKassaApiV2ErrorException::class);

        $this->api->getShiftReport();
    }
}
