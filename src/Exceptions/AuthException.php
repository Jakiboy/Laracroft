<?php

namespace Laracroft\Exceptions;

use RuntimeException;

class AuthException extends RuntimeException
{
    public function __construct(string $message, private readonly int $status = 401)
    {
        parent::__construct($message);
    }

    public function getStatus() : int
    {
        return $this->status;
    }
}
