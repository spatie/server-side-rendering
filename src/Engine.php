<?php

namespace Spatie\Ssr;

interface Engine
{
    public function run(string $script): string;

    public function getDispatchHandler(): string;
}
