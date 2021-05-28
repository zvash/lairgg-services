<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class ParticipantAcceptanceState extends Enum
{
    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const REJECTED = 'rejected';
    const RESERVED = 'reserved';
}