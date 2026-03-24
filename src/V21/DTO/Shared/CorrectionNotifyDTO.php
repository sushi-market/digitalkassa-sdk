<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\DTO\Shared;

final readonly class CorrectionNotifyDTO
{
    /** @param string[]|null $emails */
    public function __construct(
        public ?array $emails = null,
        public ?string $phone = null,
    ) {}
}
