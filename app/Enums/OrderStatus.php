<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class OrderStatus extends Enum
{
    const PENDING = 0;
    const PROCESSING = 1;
    const SHIPPED = 2;
    const CANCEL = 3;
}
