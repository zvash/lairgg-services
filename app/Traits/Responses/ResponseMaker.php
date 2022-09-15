<?php

namespace App\Traits\Responses;

use App\Enums\HttpStatusCode;
use Illuminate\Support\MessageBag;

trait ResponseMaker
{

    /**
     * @param $data
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function success($data)
    {
        return response(
            [
                'message' => 'success',
                'errors' => null,
                'status' => true,
                'data' => $data
            ], HttpStatusCode::SUCCESS
        );
    }

    /**
     * @param string $message
     * @param int $status
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function failMessage(string $message, int $status)
    {
        return response(
            [
                'message' => 'failed',
                'errors' => $message,
                'status' => false,
                'data' => []
            ], $status
        );
    }

    /**
     * @param $data
     * @param int $status
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function failData($data, int $status)
    {
        return response(
            [
                'message' => 'failed',
                'errors' => $data,
                'status' => false,
                'data' => []
            ], $status
        );
    }

    /**
     * @param $errors
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function failValidation($errors)
    {
        if ($errors instanceof MessageBag) {
            $errors = $errors->toArray();
        }
        return response(
            [
                'message' => 'failed',
                'errors' => $errors,
                'status' => false,
                'data' => []
            ], HttpStatusCode::VALIDATION_ERROR
        );
    }

    /**
     * @param string $string
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function failNotFound($string = 'Content was not found.')
    {
        return $this->failMessage($string, HttpStatusCode::NOT_FOUND);
    }

    /**
     * @param $data
     * @param int $status
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    protected function response($data, int $status)
    {
        return response(
            $data,
            HttpStatusCode::SUCCESS
        );
    }
}
