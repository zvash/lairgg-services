<?php

namespace App\Traits\Exception;

use Exception;

trait RenderProvider
{
    /**
     * Render Exception to Array.
     *
     * @param  \Exception  $exception
     * @param  string  $error
     * @param  string  $key
     * @param  string|null  $hint
     * @return array
     */
    protected function renderToArray(Exception $exception, string $error, string $key, ?string $hint = null)
    {
        $parameters = [
            'detail' => __("errors.exceptions.{$key}.detail"),
            'hint' => $hint ?? __("errors.exceptions.{$key}.hint"),
        ];

        return array_filter(
            array_merge([
                'error' => $error,
                'message' => $exception->getMessage(),
            ], $parameters)
        );
    }
}
