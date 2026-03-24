<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\Requests;

use DF\DigitalKassa\Enums\HttpAuthType;
use DF\DigitalKassa\Enums\HttpMethod;
use DF\DigitalKassa\Interfaces\ApiRequestInterface;
use DF\DigitalKassa\V21\DTO\Shift\ShiftRequestDTO;

final readonly class OpenShiftRequest implements ApiRequestInterface
{
    public function __construct(
        private int $cGroupId,
        private ?ShiftRequestDTO $requestDTO = null,
    ) {}

    public function getUri(): string
    {
        return "c_groups/$this->cGroupId/shifts/open";
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

    public function getBody(): ?object
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
