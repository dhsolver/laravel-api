<?php

namespace App\Exceptions;

use Exception;

class UntraceableTourException extends Exception
{
    public const NO_START_POINT = 1;
    public const NO_END_POINT = 2;
    public const NO_NEXT_STOP = 3;
    public const MISSING_NEXT_STOP = 4;

    protected $message;
    protected $code;
    protected $stop;
    protected $tour;

    public function __construct($tour, $stop = null, $code = 0)
    {
        $this->tour = $tour;
        $this->stop = $stop;

        switch ($code) {
            case self::NO_END_POINT:
                $message = 'Tour has no end point.';
                break;
            case self::NO_START_POINT:
                $message = 'Tour has no starting point.';
                break;
            case self::NO_START_POINT:
                $message = 'Stop has no next stop.';
                break;
            case self::NO_START_POINT:
                $message = 'Stop is missing a next stop.';
                break;
            default:
                $message = 'Untraceable Tour.';
                break;
        }

        parent::__construct($message, $code);
    }

    public function getTour()
    {
        return $this->tour;
    }

    public function getStop()
    {
        return $this->stop;
    }
}
