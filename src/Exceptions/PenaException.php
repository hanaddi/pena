<?php
namespace Hanaddi\Pena\Exceptions;

class PenaException extends \Exception {
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}