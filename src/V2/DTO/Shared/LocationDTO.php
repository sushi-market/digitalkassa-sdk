<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\DTO\Shared;

final readonly class LocationDTO
{
    public function __construct(
        public string $billing_place,
        public ?string $device_number = null,
    ) {}
}
