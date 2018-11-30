<?php

namespace App\Exceptions;

use Exception;

class MissingScoreCardException extends Exception
{
    /**
     * The error message.
     *
     * @var string
     */
    public $message;

    /**
     * MissingScoreCardException constructor.
     *
     * @param string $message
     * @param int $code
     * @return void
     */
    public function __construct($message, $code = 5666)
    {
        parent::__construct($message, $code);
    }
}
