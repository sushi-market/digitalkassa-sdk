<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\ValueObjects;

use DF\DigitalKassa\Exceptions\InvalidCredentialsException;

final readonly class Credentials
{
    public function __construct(
        public string $actorId,
        public string $actorToken,
        public int $cGroupId,
    ) {
        if ($actorId === '') {
            throw new InvalidCredentialsException('Actor ID cannot be empty');
        }

        if ($actorToken === '') {
            throw new InvalidCredentialsException('Actor token cannot be empty');
        }

        if ($cGroupId <= 0) {
            throw new InvalidCredentialsException('Group ID must be greater than zero');
        }
    }
}
