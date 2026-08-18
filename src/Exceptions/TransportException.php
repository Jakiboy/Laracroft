<?php

namespace Laracroft\Exceptions;

use Laracroft\Helpers\Response;
use RuntimeException;
use Throwable;

class TransportException extends RuntimeException
{
    public function __construct(
        string $message = Response::UNAVAILABLE,
        private readonly int $statusCode = 503,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, previous: $previous);
    }

    public static function unavailable(?Throwable $previous = null) : self
    {
        return new self(previous: $previous);
    }

    public function statusCode() : int
    {
        return $this->statusCode;
    }
}
