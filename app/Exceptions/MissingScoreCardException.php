<?php

namespace App\Exceptions;

use Exception;

class MissingScoreCardException extends Exception
{
    protected $message;

    public function __construct($message, $code = 5666)
    {
        parent::__construct($message, $code);
    }
}
