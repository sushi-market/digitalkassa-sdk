<?php

declare(strict_types=1);

namespace DF\DigitalKassa\Interfaces;

use DF\DigitalKassa\Enums\HttpAuthType;
use DF\DigitalKassa\Enums\HttpMethod;

interface ApiRequestInterface
{
    public function getUri(): string;

    public function getMethod(): HttpMethod;

    public function getAuthType(): HttpAuthType;

    public function getQuery(): ?string;

    public function getBody(): ?object;

    public function getHeaders(): array;
}
