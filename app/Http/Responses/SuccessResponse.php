<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

/**
 * Class SuccessResponse
 * @package App\Responses
 * @mixin \Illuminate\Http\Response
 */
class SuccessResponse implements Responsable
{
    protected $statusCode = 200;
    protected $message;
    protected $data;
    protected $redirect;

    public function __construct($message, $data = [], $redirect = null)
    {
        $this->message = $message;
        $this->data = $data;
        $this->redirect = $redirect;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $response = [
            'message' => $this->message,
            'data' => $this->data,
            'redirect' => $this->redirect,
        ];

        return new JsonResponse(array_filter($response), $this->statusCode);
    }
}
