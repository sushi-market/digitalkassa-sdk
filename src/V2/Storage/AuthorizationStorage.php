<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Storage;

use DF\DigitalKassa\V2\ValueObjects\Credentials;

final readonly class AuthorizationStorage
{
    public function __construct(
        Credentials $credentials,
    ) {
        $this->headerValue = 'Basic '.base64_encode(
            $credentials->actorId.':'.$credentials->actorToken,
        );
    }

    public string $headerValue;
}
