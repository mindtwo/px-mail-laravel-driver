<?php

namespace mindtwo\LaravelPxMail\Exceptions;

use Exception;

class InvalidConfigException extends Exception
{

    public function __construct(
        string $message = 'Invalid configuration provided for PxMail transport.',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
