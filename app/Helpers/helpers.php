<?php

function make_random_hash(string $salt = '')
{
    try {
        $string = bin2hex(random_bytes(32)) . $salt;
    } catch (Exception $e) {
        $string = mt_rand() . $salt;
    }
    return sha1($string);
}

function get_available_languages()
{
    $langResourcePath = resource_path('lang');
    return array_values(array_diff(scandir($langResourcePath), ['.', '..']));
}

function getToday()
{
    return \Carbon\Carbon::now('UTC')
        ->addMinutes(config('app.request_utc_offset', 0));
}

/**
 * @param string $time
 * @return float|int
 */
function convertTimeToMinutes(string $time)
{
    $timeParts = explode(':', $time);
    if (empty($timeParts[0]) || !is_numeric($timeParts[0])) {
        return 0;
    }
    $hourPartToMinute = 60 * $timeParts[0];
    $coe = 1;
    if ($hourPartToMinute < 0) {
        $coe = -1;
    }
    $minutes = abs($hourPartToMinute);
    if (!empty($timeParts[1]) && is_numeric($timeParts[1])) {
        $minutes += $timeParts[1];
    }
    return $minutes * $coe;
}
