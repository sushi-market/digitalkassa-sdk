<?php

declare(strict_types=1);

namespace DF\DigitalKassa\Exceptions;

use RuntimeException;
use Throwable;

class TransportException extends RuntimeException implements DigitalKassaException
{
    public function __construct(
        public readonly string $sdkMethod,
        public readonly string $httpMethod,
        public readonly string $uri,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: sprintf(
                'Transport error while calling %s [%s %s]',
                $this->sdkMethod,
                $this->httpMethod,
                $this->uri,
            ),
            previous: $previous,
        );
    }
}
