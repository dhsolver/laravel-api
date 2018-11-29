<?php

namespace App\Exceptions;

use Exception;

class InvalidImageException extends Exception
{
    /**
     * The error message.
     *
     * @var string
     */
    public $message;

    /**
     * InvalidImageException constructor.
     *
     * @param string $message
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
        parent::__construct();
    }
}
