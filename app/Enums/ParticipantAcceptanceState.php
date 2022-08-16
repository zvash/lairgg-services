<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class ParticipantAcceptanceState extends Enum
{
    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const REJECTED = 'rejected';
    const RESERVED = 'reserved';
    const ACCEPTED_NOT_READY = 'accepted_not_ready';
    const DISQUALIFIED = 'disqualified';
}
