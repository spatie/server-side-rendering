<?php

namespace Spatie\Ssr\Tests\Engines;

use Spatie\Ssr\Engines\Node;
use PHPUnit\Framework\TestCase;
use Spatie\Ssr\Exceptions\EngineError;

class NodeTest extends TestCase
{
    /** @var string */
    private $nodePath, $tempPath;

    public function setUp()
    {
        $this->nodePath = getenv('NODE_PATH') ?: '/usr/local/bin/node';
        $this->tempPath = __DIR__.'/../temp';
    }

    /** @test */
    public function it_can_run_a_script_and_return_its_contents()
    {
        $engine = new Node($this->nodePath, $this->tempPath);

        $result = $engine->run("console.log('Hello, world!')");

        $this->assertEquals('Hello, world!', $result);
    }

    /** @test */
    public function it_throws_an_engine_error_when_a_script_is_invalid()
    {
        $engine = new Node($this->nodePath, $this->tempPath);

        $this->expectException(EngineError::class);

        $engine->run('foo.bar.baz()');
    }

    /** @test */
    public function it_has_a_dispatch_handler()
    {
        $engine = new Node($this->nodePath, $this->tempPath);

        $this->assertEquals('console.log', $engine->getDispatchHandler());
    }
}
