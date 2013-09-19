<?php namespace Vinelab\ITunes\Exceptions;

use Exception;

class ConfigurationException extends Exception {

    public function __construct($message, $code = 1, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}