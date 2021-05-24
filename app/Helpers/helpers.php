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