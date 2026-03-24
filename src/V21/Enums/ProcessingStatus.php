<?php

declare(strict_types=1);

namespace DF\DigitalKassa\V21\Enums;

enum ProcessingStatus: string
{
    case ACCEPTED = 'accepted';
    case COMPLETED = 'completed';
}
