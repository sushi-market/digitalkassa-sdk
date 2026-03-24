<?php

declare(strict_types=1);

namespace DF\DigitalKassa\Exceptions;

use DF\DigitalKassa\V2\DTO\Shared\ErrorDTO;
use RuntimeException;

class DigitalKassaApiV21ErrorException extends RuntimeException implements DigitalKassaException
{
    /**
     * @param  ErrorDTO[]  $errors
     */
    public function __construct(
        public readonly string $sdkMethod,
        public readonly string $httpMethod,
        public readonly string $uri,
        public readonly int $statusCode,
        public readonly array $errors = [],
        public readonly mixed $rawPayload = null,
    ) {
        parent::__construct(
            message: self::buildMessage($sdkMethod, $httpMethod, $uri, $errors, $statusCode),
            code: $statusCode,
        );
    }

    /**
     * @param  ErrorDTO[]  $errors
     */
    private static function buildMessage(
        string $sdkMethod,
        string $httpMethod,
        string $uri,
        array $errors,
        int $statusCode,
    ): string {
        $details = array_map(
            static fn (ErrorDTO $error): string => trim(implode(' | ', array_filter([
                $error->type,
                $error->desc,
                $error->path,
            ]))),
            $errors,
        );

        $suffix = $details !== [] ? ': '.implode('; ', $details) : '';

        return sprintf(
            'DigitalKassa SDK error in %s [%s %s] (%d)%s',
            $sdkMethod,
            strtoupper($httpMethod),
            $uri,
            $statusCode,
            $suffix,
        );
    }
}
