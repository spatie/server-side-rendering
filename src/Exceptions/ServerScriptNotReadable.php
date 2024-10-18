<?php

namespace Spatie\Ssr\Exceptions;

use RuntimeException;

class ServerScriptNotReadable extends RuntimeException
{
    public static function atPath(string $path): self
    {
        return new self("Server script at path `{$path}` doesn't exist or isn't readable");
    }
}
