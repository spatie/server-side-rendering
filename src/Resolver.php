<?php

namespace Spatie\Ssr;

interface Resolver
{
    public function getClientScriptUrl(string $identifier): string;
    public function getServerScriptContents(string $identifier): string;
}
