<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\ValueObjects;

use DF\DigitalKassa\Exceptions\InvalidCredentialsException;

final readonly class Credentials
{
    public int $cGroupId;

    public function __construct(
        public string $actorId,
        public string $actorToken,
        int|string $cGroupId,
    ) {
        if ($this->actorId === '') {
            throw new InvalidCredentialsException('Parameter $actorId cannot be empty');
        }

        if ($this->actorToken === '') {
            throw new InvalidCredentialsException('Parameter $actorToken cannot be empty');
        }

        // В прикладном коде c_group_id часто приходит строкой из конфига,
        // поэтому принимаем numeric string, но внутри храним уже нормализованный int.
        if (is_string($cGroupId)) {
            if ($cGroupId === '' || ! ctype_digit($cGroupId)) {
                throw new InvalidCredentialsException('Parameter $cGroupId must contain only digits');
            }

            $cGroupId = (int) $cGroupId;
        }

        if ($cGroupId <= 0) {
            throw new InvalidCredentialsException('Parameter $cGroupId must be greater than zero');
        }

        $this->cGroupId = $cGroupId;
    }
}
