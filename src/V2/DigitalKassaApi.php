<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2;

use BackedEnum;
use Brick\JsonMapper\JsonMapper;
use Brick\JsonMapper\OnExtraProperties;
use Brick\JsonMapper\OnMissingProperties;
use DF\DigitalKassa\Enums\HttpAuthType;
use DF\DigitalKassa\Exceptions\DigitalKassaApiV21ErrorException;
use DF\DigitalKassa\Exceptions\InvalidRequestException;
use DF\DigitalKassa\Exceptions\TransportException;
use DF\DigitalKassa\Interfaces\ApiRequestInterface;
use DF\DigitalKassa\V2\DTO\CGroup\CGroupInfoResponseDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptInfoRequestDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptInfoResponseDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptResponseDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ItemDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptInfoRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptInfoResponseDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptRequestDTO;
use DF\DigitalKassa\V2\DTO\Receipt\ReceiptResponseDTO;
use DF\DigitalKassa\V2\DTO\Shared\AmountDTO;
use DF\DigitalKassa\V2\DTO\Shared\CorrectionNotifyDTO;
use DF\DigitalKassa\V2\DTO\Shared\ErrorDTO;
use DF\DigitalKassa\V2\DTO\Shared\NotifyDTO;
use DF\DigitalKassa\V2\DTO\Shared\OkPayloadDTO;
use DF\DigitalKassa\V2\DTO\Shared\OkShiftPayloadDTO;
use DF\DigitalKassa\V2\DTO\Shared\OkShiftStatusPayloadDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftModeRequestDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftReportResponseDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftRequestDTO;
use DF\DigitalKassa\V2\DTO\Shift\ShiftResponseDTO;
use DF\DigitalKassa\V2\Enums\ProcessingStatus;
use DF\DigitalKassa\V2\Enums\ShiftMode;
use DF\DigitalKassa\V2\Enums\ShiftStatus;
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
        $response = $this->send('getCGroupInfo', new GetCGroupInfoRequest(
            cGroupId: $this->credentials->cGroupId,
        ));

        return $this->mapJson((string) $response->getBody(), CGroupInfoResponseDTO::class);
    }

    /**
     * Создает обычный чек, предварительно валидируя входные данные и преобразуя ответ API.
     */
    public function createReceipt(ReceiptRequestDTO $requestDTO): ReceiptResponseDTO
    {
        $response = $this->send('createReceipt', new CreateReceiptRequest(
            cGroupId: $this->credentials->cGroupId,
            receiptId: $requestDTO->receipt_id,
            receiptDTO: $requestDTO->receipt,
        ));

        return $this->mapReceiptResponse($response, ReceiptResponseDTO::class);
    }

    /**
     * Запрашивает информацию о ранее созданном чеке по его идентификатору.
     */
    public function getReceiptInfo(ReceiptInfoRequestDTO $requestDTO): ReceiptInfoResponseDTO
    {
        $response = $this->send('getReceiptInfo', new GetReceiptInfoRequest(
            cGroupId: $this->credentials->cGroupId,
            receiptId: $requestDTO->receipt_id,
        ));

        return $this->mapReceiptResponse($response, ReceiptInfoResponseDTO::class);
    }

    /**
     * Создает чек коррекции после проверки обязательных полей и формата данных.
     */
    public function createCorrectionReceipt(CorrectionReceiptRequestDTO $requestDTO): CorrectionReceiptResponseDTO
    {
        $response = $this->send('createCorrectionReceipt', new CreateCorrectionReceiptRequest(
            cGroupId: $this->credentials->cGroupId,
            receiptId: $requestDTO->receipt_id,
            receiptDTO: $requestDTO->correction_receipt,
        ));

        return $this->mapCorrectionReceiptResponse($response, CorrectionReceiptResponseDTO::class);
    }

    /**
     * Получает статус и результат обработки ранее отправленного чека коррекции.
     */
    public function getCorrectionReceiptInfo(CorrectionReceiptInfoRequestDTO $requestDTO): CorrectionReceiptInfoResponseDTO
    {
        $response = $this->send('getCorrectionReceiptInfo', new GetCorrectionReceiptInfoRequest(
            cGroupId: $this->credentials->cGroupId,
            receiptId: $requestDTO->receipt_id,
        ));

        return $this->mapCorrectionReceiptResponse($response, CorrectionReceiptInfoResponseDTO::class);
    }

    /**
     * Возвращает текущее состояние смены: статус, номер смены, номер чека и режим работы.
     */
    public function getShiftReport(): ShiftReportResponseDTO
    {
        $response = $this->send('getShiftReport', new GetShiftReportRequest(
            cGroupId: $this->credentials->cGroupId,
        ));

        /** @var OkShiftStatusPayloadDTO $payload */
        $payload = $this->mapJson((string) $response->getBody(), OkShiftStatusPayloadDTO::class);

        return new ShiftReportResponseDTO(
            shift_status: $payload->shift_status !== null ? ShiftStatus::from($payload->shift_status) : null,
            shift_number: $payload->shift_number,
            check_number: $payload->check_number,
            mode: $payload->mode !== null ? ShiftMode::from($payload->mode) : null,
        );
    }

    /**
     * Открывает смену на кассе и возвращает фискальные реквизиты операции.
     */
    public function openShift(?ShiftRequestDTO $requestDTO = null): ShiftResponseDTO
    {
        $response = $this->send('openShift', new OpenShiftRequest(
            cGroupId: $this->credentials->cGroupId,
            requestDTO: $requestDTO,
        ));

        return $this->mapShiftResponse($response);
    }

    /**
     * Закрывает смену и возвращает данные сформированного отчета о закрытии.
     */
    public function closeShift(?ShiftRequestDTO $requestDTO = null): ShiftResponseDTO
    {
        $response = $this->send('closeShift', new CloseShiftRequest(
            cGroupId: $this->credentials->cGroupId,
            requestDTO: $requestDTO,
        ));

        return $this->mapShiftResponse($response);
    }

    /**
     * Меняет режим смены, например переводит кассу между обычным и автономным режимом.
     */
    public function changeShiftMode(ShiftModeRequestDTO $requestDTO): ShiftResponseDTO
    {
        $response = $this->send('changeShiftMode', new ChangeShiftModeRequest(
            cGroupId: $this->credentials->cGroupId,
            requestDTO: $requestDTO,
        ));

        return $this->mapShiftResponse($response);
    }

    /**
     * Выполняет HTTP-запрос к API, добавляет авторизацию и преобразует сетевые ошибки в исключения SDK.
     */
    private function send(string $sdkMethod, ApiRequestInterface $request): ResponseInterface
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
                    sdkMethod: $sdkMethod,
                    request: $request,
                    response: $response,
                );
            }

            throw new TransportException(
                sdkMethod: $sdkMethod,
                httpMethod: strtoupper($request->getMethod()->value),
                uri: $uri,
                previous: $e,
            );
        } catch (Exception $e) {
            throw new TransportException(
                sdkMethod: $sdkMethod,
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

        /** @var array<string, string> $headers */
        $headers = array_merge($headers, $request->getHeaders());

        return $headers;
    }

    /**
     * Преобразует JSON-строку ответа API в экземпляр указанного DTO-класса.
     *
     * @template T of object
     *
     * @param  class-string<T>  $className
     * @return T
     */
    private function mapJson(string $json, string $className): object
    {
        return $this->mapper->map($json, $className);
    }

    /**
     * Преобразует ответ API по обычному чеку в DTO с учетом асинхронного статуса обработки.
     *
     * @template T of ReceiptResponseDTO|ReceiptInfoResponseDTO
     *
     * @param  class-string<T>  $className
     * @return T
     */
    private function mapReceiptResponse(ResponseInterface $response, string $className): object
    {
        // DigitalKassa может принять чек в обработку и вернуть 202 без payload.
        if ($response->getStatusCode() === 202) {
            return new $className(status: ProcessingStatus::ACCEPTED);
        }

        /** @var OkPayloadDTO $payload */
        $payload = $this->mapJson((string) $response->getBody(), OkPayloadDTO::class);

        return new $className(
            status: ProcessingStatus::COMPLETED,
            doc: $payload->doc,
            cashbox: $payload->cashbox,
            service: $payload->service,
        );
    }

    /**
     * Преобразует ответ API по чеку коррекции в DTO с учетом промежуточного статуса обработки.
     *
     * @template T of CorrectionReceiptResponseDTO|CorrectionReceiptInfoResponseDTO
     *
     * @param  class-string<T>  $className
     * @return T
     */
    private function mapCorrectionReceiptResponse(ResponseInterface $response, string $className): object
    {
        // Для чека коррекции логика статусов такая же, как и для обычного чека.
        if ($response->getStatusCode() === 202) {
            return new $className(status: ProcessingStatus::ACCEPTED);
        }

        /** @var OkPayloadDTO $payload */
        $payload = $this->mapJson((string) $response->getBody(), OkPayloadDTO::class);

        return new $className(
            status: ProcessingStatus::COMPLETED,
            doc: $payload->doc,
            cashbox: $payload->cashbox,
            service: $payload->service,
        );
    }

    /**
     * Преобразует ответ API по операциям со сменой в DTO с фискальными реквизитами.
     */
    private function mapShiftResponse(ResponseInterface $response): ShiftResponseDTO
    {
        /** @var OkShiftPayloadDTO $payload */
        $payload = $this->mapJson((string) $response->getBody(), OkShiftPayloadDTO::class);

        return new ShiftResponseDTO(
            shift_number: $payload->shift_number,
            fd_number: $payload->fd_number,
            fiscal_sign: $payload->fiscal_sign,
        );
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
