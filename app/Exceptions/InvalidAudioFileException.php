<?php

namespace App\Exceptions;

use Exception;

class InvalidAudioFileException extends Exception
{
    /**
     * The error message.
     *
     * @var string
     */
    public $message;

    /**
     * InvalidAudioFileException constructor.
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
