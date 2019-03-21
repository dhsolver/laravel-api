<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'Object not found',
            ], 404);
        }

        if ($exception instanceof UnauthorizedHttpException) {
            return response()->json([
                'message' => $exception->getMessage(),
                'code' => 401,
            ], 401);
        }

        if ($this->shouldntReport($exception)) {
            return parent::render($request, $exception);
        }

        if (config('app.env') == 'local') {
            return parent::render($request, $exception);
        }

        $code = isset($exception->status) ? $exception->status : null;
        if (empty($code)) {
            $code = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
        }

        return response()->json([
            'message' => $exception->getMessage(),
            'code' => $code,
        ], $code);
    }
}
