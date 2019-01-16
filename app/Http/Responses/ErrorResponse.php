<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

/**
 * Class ErrorResponse
 * @package App\Responses
 * @mixin \Illuminate\Http\Response
 */
class ErrorResponse implements Responsable
{
    /**
     * The HTTP status code for the response.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * The response message or message array.
     *
     * @var mixed
     */
    protected $messages;

    /**
     * The data object to return with the response.
     *
     * @var array
     */
    protected $data;

    /**
     * ErrorResponse constructor.
     *
     * @param int $statusCode
     * @param mixed $messages
     * @param array $data
     */
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
        if (! empty($this->data)) {
            $response['data'] = $this->data;
        }

        return response()->json($response, $this->statusCode);
    }
}
