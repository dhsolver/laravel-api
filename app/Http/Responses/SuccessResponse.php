<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;

/**
 * Class SuccessResponse
 * @package App\Responses
 * @mixin \Illuminate\Http\Response
 */
class SuccessResponse implements Responsable
{
    /**
     * The HTTP status code for the response.
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * The response message.
     *
     * @var string
     */
    protected $message;

    /**
     * The data object to return with the response.
     *
     * @var array
     */
    protected $data;

    /**
     * The redirect URL to return with the response.
     *
     * @var string
     */
    protected $redirect;

    /**
     * SuccessResponse constructor.
     *
     * @param string $message
     * @param array $data
     * @param string $redirect
     */
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

        return response()->json(array_filter($response), $this->statusCode);
    }
}
