<?php

namespace Spatie\Ssr\Tests\Engines;

use PHPUnit\Framework\TestCase;
use Spatie\Ssr\Engines\V8;
use Spatie\Ssr\Exceptions\EngineError;
use V8Js;

class V8Test extends TestCase
{
    public function setUp()
    {
        if (!extension_loaded('v8js')) {
            $this->markTestSkipped('The V8Js extension is not available.');
        }
    }

    /** @test */
    public function it_can_run_a_script_and_return_its_contents()
    {
        $engine = new V8(new V8Js());

        $result = $engine->run("print('Hello, world!')");

        $this->assertEquals('Hello, world!', $result);
    }

    /** @test */
    public function it_throws_an_engine_error_when_a_script_is_invalid()
    {
        $engine = new V8(new V8Js());

        $this->expectException(EngineError::class);

        $engine->run('foo.bar.baz()');
    }

    /** @test */
    public function it_has_a_dispatch_handler()
    {
        $engine = new V8(new V8Js());

        $this->assertEquals('print', $engine->getDispatchHandler());
    }
}
