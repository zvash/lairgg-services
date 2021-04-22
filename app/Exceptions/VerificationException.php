<?php

namespace App\Exceptions;

use App\Traits\Exception\RenderProvider;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class VerificationException extends UnprocessableEntityHttpException
{
    use RenderProvider;

    /**
     * Key for localization message type.
     *
     * @var string
     */
    private $key;

    /**
     * Exception constructor.
     *
     * @param  string  $message
     * @param  string  $key
     * @return void
     */
    public function __construct(string $message, string $key)
    {
        parent::__construct($message);

        $this->key = $key;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            $response = $this->renderToArray(
                $this, 'invalid_verification', 'verification.'.$this->key
            );

            return response()->json($response, $this->getStatusCode());
        }
    }
}
