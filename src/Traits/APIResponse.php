<?php

namespace Codepane\LaravelApiRepository\Traits;

trait APIResponse
{
    /**
     * Core of response
     *
     * @param   string          $message
     * @param   array|object    $data
     * @param   integer|string  $statusCode
     * @param   boolean         $isSuccess
     */
    public function coreResponse($message, $data = null, $statusCode, $isSuccess = true)
    {
        // Check the params
        if(!$message) return response()->json(['message' => 'Message is required'], config('custom.api.http_codes.error.internal_server'));

        // Send the response
        if($isSuccess) {
            return response()->json([
                'message' => $message,
                'error' => false,
                'code' => $statusCode,
                'data' => $data
            ], $statusCode);
        } else {
            return response()->json([
                'message' => $message,
                'error' => true,
                'code' => $statusCode,
                'data' => $data
            ], $statusCode);
        }
    }

    /**
     * Send any success response
     *
     * @param   string          $message
     * @param   array|object    $data
     * @param   integer|string  $statusCode
     */
    public function success($message, $data, $statusCode = null)
    {
        $statusCode = is_null($statusCode) ? config('laravel-api-repository.api.http_codes.success') : $statusCode;

        return $this->coreResponse($message, $data, $statusCode);
    }

    /**
     * Send any error response
     *
     * @param   string          $message
     * @param   integer|string         $statusCode
     */
    public function error($message, $statusCode = null, $data = null)
    {   
        $statusCode = is_null($statusCode) ? config('custom.api.http_codes.error.internal_server') : $statusCode;
        return $this->coreResponse($message, $data, $statusCode, false);
    }
}