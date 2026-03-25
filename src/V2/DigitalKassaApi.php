<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2;

use BackedEnum;
use Brick\JsonMapper\JsonMapper;
use Brick\JsonMapper\OnExtraProperties;
use Brick\JsonMapper\OnMissingProperties;
use DF\DigitalKassa\Enums\HttpAuthType;
use DF\DigitalKassa\Exceptions\DigitalKassaApiV21ErrorException;
use DF\DigitalKassa\Exceptions\TransportException;
use DF\DigitalKassa\Interfaces\ApiRequestInterface;
use DF\DigitalKassa\V2\DTO\CGroup\CGroupInfoResponseDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptInfoRequestDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptInfoResponseDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptResponseDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptInfoRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptInfoResponseDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptResponseDTO;
use DF\DigitalKassa\V2\DTO\Shared\ErrorDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftModeRequestDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftReportResponseDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftRequestDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftResponseDTO;
use DF\DigitalKassa\V2\Requests\ChangeShiftModeRequest;
use DF\DigitalKassa\V2\Requests\CloseShiftRequest;
use DF\DigitalKassa\V2\Requests\CreateCorrectionReceiptRequest;
use DF\DigitalKassa\V2\Requests\CreateReceiptRequest;
use DF\DigitalKassa\V2\Requests\GetCGroupInfoRequest;
use DF\DigitalKassa\V2\Requests\GetCorrectionReceiptInfoRequest;
use DF\DigitalKassa\V2\Requests\GetReceiptInfoRequest;
use DF\DigitalKassa\V2\Requests\GetShiftReportRequest;
use DF\DigitalKassa\V2\Requests\OpenShiftRequest;
use DF\DigitalKassa\V2\Storage\AuthorizationStorage;
use DF\DigitalKassa\V2\ValueObjects\Credentials;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

final readonly class DigitalKassaApi
{
    private JsonMapper $mapper;

    private ClientInterface $httpClient;

    private AuthorizationStorage $authorizationStorage;

    /**
     * Создает API-клиент DigitalKassa v2.1 и подготавливает зависимости для работы с HTTP и JSON.
     */
    public function __construct(
        private Credentials $credentials,
        ?ClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => 'https://api.digitalkassa.ru/v2.1/',
        ]);

        $this->authorizationStorage = new AuthorizationStorage($this->credentials);
        $this->mapper = new JsonMapper(
            onExtraProperties: OnExtraProperties::Ignore,
            onMissingProperties: OnMissingProperties::SetDefault,
        );
    }

    /**
     * Получает информацию о кассовой группе, указанной в учетных данных клиента.
     */
    public function getCGroupInfo(): CGroupInfoResponseDTO
    {
        $json = $this->send(
            request: new GetCGroupInfoRequest(
                cGroupId: $this->credentials->cGroupId,
            ),
        )->getBody()->getContents();

        return $this->mapper->map($json, CGroupInfoResponseDTO::class);
    }

    /**
     * Создает обычный чек, предварительно валидируя входные данные и преобразуя ответ API.
     */
    public function createReceipt(ReceiptRequestDTO $requestDTO): ReceiptResponseDTO
    {
        $json = $this->send(
            request: new CreateReceiptRequest(
                cGroupId: $this->credentials->cGroupId,
                receiptId: $requestDTO->receipt_id,
                receiptDTO: $requestDTO->receipt,
            ),
        )->getBody()->getContents();

        return $this->mapper->map($json, ReceiptResponseDTO::class);
    }

    /**
     * Запрашивает информацию о ранее созданном чеке по его идентификатору.
     */
    public function getReceiptInfo(ReceiptInfoRequestDTO $requestDTO): ReceiptInfoResponseDTO
    {
        $json = $this->send(
            request: new GetReceiptInfoRequest(
                cGroupId: $this->credentials->cGroupId,
                receiptId: $requestDTO->receipt_id,
            ),
        )->getBody()->getContents();

        return $this->mapper->map($json, ReceiptInfoResponseDTO::class);
    }

    /**
     * Создает чек коррекции после проверки обязательных полей и формата данных.
     */
    public function createCorrectionReceipt(CorrectionReceiptRequestDTO $requestDTO): CorrectionReceiptResponseDTO
    {
        $json = $this->send(
            request: new CreateCorrectionReceiptRequest(
                cGroupId: $this->credentials->cGroupId,
                receiptId: $requestDTO->receipt_id,
                receiptDTO: $requestDTO->correction_receipt,
            ),
        )->getBody()->getContents();

        return $this->mapper->map($json, CorrectionReceiptResponseDTO::class);
    }

    /**
     * Получает статус и результат обработки ранее отправленного чека коррекции.
     */
    public function getCorrectionReceiptInfo(CorrectionReceiptInfoRequestDTO $requestDTO): CorrectionReceiptInfoResponseDTO
    {
        $json = $this->send(
            request: new GetCorrectionReceiptInfoRequest(
                cGroupId: $this->credentials->cGroupId,
                receiptId: $requestDTO->receipt_id,
            ),
        )->getBody()->getContents();

        return $this->mapper->map($json, CorrectionReceiptInfoResponseDTO::class);
    }

    /**
     * Возвращает текущее состояние смены: статус, номер смены, номер чека и режим работы.
     */
    public function getShiftReport(): ShiftReportResponseDTO
    {
        $json = $this->send(
            request: new GetShiftReportRequest(
                cGroupId: $this->credentials->cGroupId,
            ),
        )->getBody()->getContents();

        return $this->mapper->map($json, ShiftReportResponseDTO::class);
    }

    /**
     * Открывает смену на кассе и возвращает фискальные реквизиты операции.
     */
    public function openShift(?ShiftRequestDTO $requestDTO = null): ShiftResponseDTO
    {
        $json = $this->send(
            request: new OpenShiftRequest(
                cGroupId: $this->credentials->cGroupId,
                requestDTO: $requestDTO,
            ),
        )->getBody()->getContents();

        return $this->mapper->map($json, ShiftResponseDTO::class);
    }

    /**
     * Закрывает смену и возвращает данные сформированного отчета о закрытии.
     */
    public function closeShift(?ShiftRequestDTO $requestDTO = null): ShiftResponseDTO
    {
        $json = $this->send(
            request: new CloseShiftRequest(
                cGroupId: $this->credentials->cGroupId,
                requestDTO: $requestDTO,
            ),
        )->getBody()->getContents();

        return $this->mapper->map($json, ShiftResponseDTO::class);
    }

    /**
     * Меняет режим смены, например переводит кассу между обычным и автономным режимом.
     */
    public function changeShiftMode(ShiftModeRequestDTO $requestDTO): ShiftResponseDTO
    {
        $json = $this->send(
            request: new ChangeShiftModeRequest(
                cGroupId: $this->credentials->cGroupId,
                requestDTO: $requestDTO,
            ),
        )->getBody()->getContents();

        return $this->mapper->map($json, ShiftResponseDTO::class);
    }

    /**
     * Выполняет HTTP-запрос к API, добавляет авторизацию и преобразует сетевые ошибки в исключения SDK.
     */
    private function send(ApiRequestInterface $request): ResponseInterface
    {
        // Весь transport централизован здесь: request-объекты описывают endpoint,
        // а клиент добавляет auth, сериализует DTO и нормализует ошибки сети/API.
        $uri = $request->getUri();

        if ($request->getQuery() !== null) {
            $uri .= '?'.ltrim($request->getQuery(), '?');
        }

        $options = [
            RequestOptions::HTTP_ERRORS => true,
            RequestOptions::HEADERS => $this->buildHeaders($request),
        ];

        if ($request->getBody() !== null) {
            $serializedBody = $this->serializeJsonToArray($request->getBody());

            if ($serializedBody !== []) {
                $options[RequestOptions::JSON] = $serializedBody;
            }
        }

        try {
            return $this->httpClient->request($request->getMethod()->value, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();

            if ($response !== null) {
                throw $this->normalizeApiError(
                    sdkMethod: get_class($request),
                    request: $request,
                    response: $response,
                );
            }

            throw new TransportException(
                sdkMethod: get_class($request),
                httpMethod: strtoupper($request->getMethod()->value),
                uri: $uri,
                previous: $e,
            );
        } catch (Exception $e) {
            throw new TransportException(
                sdkMethod: get_class($request),
                httpMethod: strtoupper($request->getMethod()->value),
                uri: $uri,
                previous: $e,
            );
        }
    }

    /**
     * Собирает HTTP-заголовки запроса и при необходимости добавляет заголовок авторизации.
     *
     * @return array<string, string>
     */
    private function buildHeaders(ApiRequestInterface $request): array
    {
        $headers = [
            'Content-Type' => 'application/json; charset=utf-8',
        ];

        if ($request->getAuthType() === HttpAuthType::BASIC) {
            $headers['Authorization'] = $this->authorizationStorage->headerValue;
        }

        return array_merge($headers, $request->getHeaders());
    }

    /**
     * Нормализует ошибку API в типизированное исключение SDK и сохраняет детали исходного ответа.
     */
    private function normalizeApiError(
        string $sdkMethod,
        ApiRequestInterface $request,
        ResponseInterface $response,
    ): DigitalKassaApiV21ErrorException {
        $rawBody = (string) $response->getBody();
        $decoded = json_decode($rawBody, true);

        $errors = [];

        // В документации ошибки приходят массивом объектов, но raw payload тоже сохраняем,
        // чтобы не потерять детали, если shape ответа отличается от ожидаемого.
        if (is_array($decoded) && array_is_list($decoded)) {
            foreach ($decoded as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $errors[] = new ErrorDTO(
                    type: isset($item['type']) && is_string($item['type']) ? $item['type'] : null,
                    desc: isset($item['desc']) && is_string($item['desc']) ? $item['desc'] : '',
                    path: isset($item['path']) && is_string($item['path']) ? $item['path'] : null,
                );
            }
        }

        return new DigitalKassaApiV21ErrorException(
            sdkMethod: $sdkMethod,
            httpMethod: strtoupper($request->getMethod()->value),
            uri: $request->getUri(),
            statusCode: $response->getStatusCode(),
            errors: $errors,
            rawPayload: $decoded ?? $rawBody,
        );
    }

    /**
     * Рекурсивно преобразует DTO и enum в массив, пригодный для JSON-сериализации через HTTP-клиент.
     *
     * @param  object|array<array-key, mixed>  $json
     * @return array<array-key, mixed>
     */
    private function serializeJsonToArray(object|array $json): array
    {
        /** @var array<array-key, mixed> $result */
        $result = (array) $json;

        // Рекурсивно разворачиваем DTO и enum в plain array для Guzzle JSON request.
        foreach ($result as $key => $item) {
            $result[$key] = match (true) {
                $item instanceof BackedEnum => $item->value,
                is_object($item), is_array($item) => $this->serializeJsonToArray($item),
                default => $item,
            };
        }

        /** @var array<array-key, mixed> $result */
        $result = array_filter($result, static fn (mixed $value): bool => $value !== null);

        return $result;
    }
}
