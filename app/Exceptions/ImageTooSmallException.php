<?php

namespace App\Exceptions;

use Exception;

class ImageTooSmallException extends Exception
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
        parent::__construct();
    }
}
