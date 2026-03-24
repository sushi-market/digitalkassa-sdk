<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Requests;

use DF\DigitalKassa\Enums\HttpAuthType;
use DF\DigitalKassa\Enums\HttpMethod;
use DF\DigitalKassa\Interfaces\ApiRequestInterface;
use DF\DigitalKassa\V2\DTO\CorrectionReceipt\CorrectionReceiptDTO;

final readonly class CreateCorrectionReceiptRequest implements ApiRequestInterface
{
    public function __construct(
        private int $cGroupId,
        private string $receiptId,
        private CorrectionReceiptDTO $receiptDTO,
    ) {}

    public function getUri(): string
    {
        return "c_groups/$this->cGroupId/receipts/correction/$this->receiptId";
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

    public function getBody(): CorrectionReceiptDTO
    {
        return $this->receiptDTO;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }
}
