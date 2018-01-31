<?php

namespace Spatie\Ssr\Tests\Renderer;

use Spatie\Ssr\Renderer;
use Spatie\Ssr\Engines\Node;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var \Spatie\Ssr\Renderer */
    protected $renderer;

    public function setUp()
    {
        $nodePath = getenv('NODE_PATH') ?: '/usr/local/bin/node';
        $tempPath = __DIR__.'/../temp';

        $engine = new Node($nodePath, $tempPath);

        $this->renderer = (new Renderer($engine))->debug();
    }
}
