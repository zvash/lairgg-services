<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

class TournamentStructure extends Enum
{
    const SIX = '6v6';
    const FIVE = '5v5';
    const FOUR = '4v4';
    const THREE = '3v3';
    const TWO = '2v2';
    const ONE = '1v1';
    const OTHER = 'Other';
}
