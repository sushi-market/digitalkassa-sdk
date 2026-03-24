<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Requests;

use DF\DigitalKassa\Enums\HttpAuthType;
use DF\DigitalKassa\Enums\HttpMethod;
use DF\DigitalKassa\Interfaces\ApiRequestInterface;

final readonly class GetShiftReportRequest implements ApiRequestInterface
{
    public function __construct(
        private int $cGroupId,
    ) {}

    public function getUri(): string
    {
        return "c_groups/$this->cGroupId/shifts/report";
    }

    public function getMethod(): HttpMethod
    {
        return HttpMethod::GET;
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
        return null;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }
}
