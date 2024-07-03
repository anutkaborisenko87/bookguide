<?php

namespace App\Exception;

use Exception;

class ValidationException extends Exception
{
    private array $errors;

    public function __construct(array $errors, $message = 'Validation errors', Exception $previous = null)
    {
        parent::__construct($message, 422, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
