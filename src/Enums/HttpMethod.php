<?php

declare(strict_types=1);

namespace DF\DigitalKassa\Enums;

enum HttpMethod: string
{
    case GET = 'get';
    case HEAD = 'head';
    case OPTIONS = 'options';
    case TRACE = 'trace';
    case PUT = 'put';
    case DELETE = 'delete';
    case POST = 'post';
    case PATCH = 'patch';
    case CONNECT = 'connect';
}
