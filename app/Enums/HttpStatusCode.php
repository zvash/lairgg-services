<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class HttpStatusCode extends Enum
{
    const SUCCESS = 200;
    const NOT_FOUND = 404;
    const VALIDATION_ERROR = 422;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
}