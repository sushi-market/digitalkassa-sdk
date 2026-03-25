<?php

declare(strict_types=1);

namespace Tests\Feature\V2;

use DF\DigitalKassa\V2\DigitalKassaApi;
use DF\DigitalKassa\V2\DTO\CGroup\CGroupInfoResponseDTO;
use DF\DigitalKassa\V2\ValueObjects\Credentials;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetCGroupInfoTest extends TestCase
{
    private const int C_GROUP_ID = 123;

    private ClientInterface&MockObject $httpClient;

    private DigitalKassaApi $api;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);

        $credentials = new Credentials(
            actorId: 'test-login',
            actorToken: 'test-password',
            cGroupId: self::C_GROUP_ID,
        );

        $this->api = new DigitalKassaApi(
            credentials: $credentials,
            httpClient: $this->httpClient,
        );
    }

    /**
     * Happy path: API вернул валидный JSON — метод возвращает заполненный DTO.
     */
    public function test_get_c_group_info_returns_mapped_dto(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn(new Response(
                status: 200,
                body: json_encode([
                    'type' => 'online_store',
                    'taxation' => 2,
                    'billing_place_list' => [
                        'https://example.com/',
                    ],
                ]),
            ));

        $result = $this->api->getCGroupInfo();

        /** @noinspection PhpConditionAlreadyCheckedInspection */
        $this->assertInstanceOf(CGroupInfoResponseDTO::class, $result);
    }
}
