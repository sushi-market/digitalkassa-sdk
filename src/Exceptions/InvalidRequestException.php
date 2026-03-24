<?php

declare(strict_types=1);

namespace DF\DigitalKassa\Exceptions;

use InvalidArgumentException;

class InvalidRequestException extends InvalidArgumentException implements DigitalKassaException {}
