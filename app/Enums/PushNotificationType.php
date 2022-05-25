<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class PushNotificationType extends Enum
{
    const MESSAGE = 'message';
    const GEM = 'gem';
    const PRIZE = 'prize';
    const TOURNAMENT_INVITATION = 'tournament_invitation';
    const TEAM_INVITATION = 'team_invitation';
    const TOURNAMENT = 'tournament';
    const TEAM = 'team';
    const ORDER = 'order';
    const MATCH = 'match';
    const MATCH_LOBBY = 'match_lobby';
    const TOURNAMENT_LOBBY = 'tournament_lobby';
}
