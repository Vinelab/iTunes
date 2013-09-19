<?php namespace Vinelab\ITunes\Exceptions;

use Exception;

class InvalidSearchException extends Exception {

    public function __construct($message, $code = 2, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}