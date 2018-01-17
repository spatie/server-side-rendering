<?php

namespace Spatie\Ssr\Exceptions;

use Exception;
use RuntimeException;

class EngineError extends RuntimeException
{
    /** @var \Exception */
    protected $originalException;

    public static function withException(Exception $exception): self
    {
        $error = new self();

        $error->originalException = $exception;

        return $error;
    }

    public function getException(): Exception
    {
        return $this->originalException ?? $this;
    }
}
