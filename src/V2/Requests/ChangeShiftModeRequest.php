<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Requests;

use DF\DigitalKassa\Enums\HttpAuthType;
use DF\DigitalKassa\Enums\HttpMethod;
use DF\DigitalKassa\Interfaces\ApiRequestInterface;
use DF\DigitalKassa\V2\DTO\Shift\ShiftModeRequestDTO;

final readonly class ChangeShiftModeRequest implements ApiRequestInterface
{
    public function __construct(
        private int $cGroupId,
        private ShiftModeRequestDTO $requestDTO,
    ) {}

    public function getUri(): string
    {
        return "c_groups/$this->cGroupId/shifts/mode";
    }

    public function getMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    public function getAuthType(): HttpAuthType
    {
        return HttpAuthType::BASIC;
    }

    public function getQuery(): ?string
    {
        return null;
    }

    public function getBody(): ShiftModeRequestDTO
    {
        return $this->requestDTO;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }
}
