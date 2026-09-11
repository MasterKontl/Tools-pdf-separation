<?php

namespace App\Exceptions;

use Exception;

class ConversionException extends Exception
{
    public function __construct(
        string $errorType,
        string $message = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
        $this->errorType = $errorType;
    }

    public function getErrorType(): string
    {
        return $this->errorType;
    }
}
