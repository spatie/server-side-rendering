<?php

namespace Spatie\Ssr;

interface Engine
{
    /**
     * @param string $script
     *
     * @return string
     */
    public function run(string $script): string;
}
