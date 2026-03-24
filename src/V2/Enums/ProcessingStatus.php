<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V2\Enums;

enum ProcessingStatus: string
{
    case ACCEPTED = 'accepted';
    case COMPLETED = 'completed';
}
