<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

/**
 * Class ErrorResponse
 * @package App\Responses
 * @mixin \Illuminate\Http\Response
 */
class ErrorResponse implements Responsable
{
    protected $statusCode;
    protected $messages;
    protected $data;

    public function __construct($statusCode, $messages, $data = [])
    {
        $this->messages = $messages;
        $this->statusCode = $statusCode;
        $this->data = $data;
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
        $response = [];
        if (is_array($this->messages)) {
            $response['message'] = current($this->messages);
            $response['errors'] = $this->messages;
        } else {
            $response['message'] = (string) $this->messages;
        }
        if (count($this->data)) {
            $response['data'] = $this->data;
        }
        return new JsonResponse($response, $this->statusCode);
    }
}
