<?php

namespace App\Exceptions;

use Exception;

class UntraceableTourException extends Exception
{
    public const NO_START_POINT = 1;
    public const NO_END_POINT = 2;
    public const NO_NEXT_STOP = 3;
    public const MISSING_NEXT_STOP = 4;
    public const STOP_MISSING_LOCATION = 5;
    public const STOP_MISSING_ROUTE = 6;
    public const TOUR_MISSING_ROUTE = 7;

    /**
     * The error message.
     *
     * @var string
     */
    protected $message;

    /**
     * The error code.
     *
     * @var int
     */
    protected $code;

    /**
     * The Stop related to the error.
     *
     * @var \App\Stop
     */
    protected $stop;

    /**
     * The Tour related to the error.
     *
     * @var \App\Tour
     */
    protected $tour;

    /**
     * UntraceableTourException constructor.
     *
     * @param \App\Tour $tour
     * @param \App\Stop $stop
     * @param int $code
     */
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
            case self::NO_NEXT_STOP:
                $message = 'Stop has no next stop.';
                break;
            case self::MISSING_NEXT_STOP:
                $message = 'Stop is missing a next stop.';
                break;
            case self::STOP_MISSING_LOCATION:
                $message = "Stop ID {$stop->id} has no location.";
                break;
            default:
                $message = 'Untraceable Tour.';
                break;
        }
        parent::__construct($message, $code);
    }

    /**
     * Get the Tour related to the error.
     *
     * @return \App\Tour
     */
    public function getTour()
    {
        return $this->tour;
    }

    /**
     * Get the Stop related to the error.
     *
     * @return \App\Stop
     */
    public function getStop()
    {
        return $this->stop;
    }
}
