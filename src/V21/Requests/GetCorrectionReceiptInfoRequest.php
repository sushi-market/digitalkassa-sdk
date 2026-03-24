<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\Requests;

use DF\DigitalKassa\Enums\HttpAuthType;
use DF\DigitalKassa\Enums\HttpMethod;
use DF\DigitalKassa\Interfaces\ApiRequestInterface;

final readonly class GetCorrectionReceiptInfoRequest implements ApiRequestInterface
{
    public function __construct(
        private int $cGroupId,
        private string $receiptId,
    ) {}

    public function getUri(): string
    {
        return "c_groups/$this->cGroupId/receipts/correction/$this->receiptId";
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
