<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class CashOutStatus extends Enum
{
    const PENDING = 0;
    const ACCEPTED = 1;
    const DENIED = 2;
}
