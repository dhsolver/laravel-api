<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Helper function for success response.
     *
     * @param string $message
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    public function success($message, $data = [])
    {
        return new SuccessResponse($message, $data);
    }

    /**
     * Helper function for error response
     *
     * @param int $statusCode
     * @param string $messages
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    public function fail($statusCode = 500, $messages = '', $data = [])
    {
        if (empty($messages)) {
            $messages = 'An unexpected error occurred.  Please try again.';
        }

        return new ErrorResponse($statusCode, $messages, $data);
    }

    public function test()
    {
        phpinfo();
        // $filename = '../../../../resources/assets/test.jpg';

        // $img = \Image::make($filename)
        //     ->resize(100, 100);

        // return $img->response('jpg');
    }
}
