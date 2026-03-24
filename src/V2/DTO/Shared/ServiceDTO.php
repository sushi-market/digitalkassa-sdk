<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

final readonly class ServiceDTO
{
    public function __construct(
        public ?string $callback_url = null,
        public ?string $receipt_url = null,
    ) {}
}
