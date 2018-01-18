<?php

namespace Spatie\Ssr\Tests\Renderer;

use Spatie\Ssr\Renderer;
use Spatie\Ssr\Engines\Node;
use Spatie\Ssr\Resolvers\PathResolver;
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

        $scriptsPath = __DIR__.'/../scripts';
        $publicPath = '/js';

        $resolver = new PathResolver($scriptsPath, $publicPath);

        $this->renderer = new Renderer($engine, $resolver);
    }
}
