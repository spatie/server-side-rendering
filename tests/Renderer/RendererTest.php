<?php

namespace Spatie\Ssr\Tests\Renderer;

use Spatie\Ssr\Exceptions\ServerScriptNotReadable;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RendererTest extends TestCase
{
    /** @test */
    public function it_can_render_a_javascript_app()
    {
        $result = $this->renderer
            ->entry(__DIR__.'/../scripts/app-server.js')
            ->render();

        $this->assertEquals(
            '<p>Hello, world!</p>',
            $result
        );
    }

    /** @test */
    public function it_can_decode_json()
    {
        $result = $this->renderer
            ->entry(__DIR__.'/../scripts/app-with-json-server.js')
            ->render();

        $this->assertEquals(
            ['foo' => 'bar'],
            $result
        );
    }

    /** @test */
    public function it_renders_a_fallback_when_disabled()
    {
        $result = $this->renderer
            ->entry(__DIR__.'/../scripts/app-server.js')
            ->fallback('<div id="app"></div>')
            ->enabled(false)
            ->render();

        $this->assertEquals(
            '<div id="app"></div>',
            $result
        );
    }

    /** @test */
    public function it_renders_a_fallback_when_server_rendering_fails_and_debug_is_disabled()
    {
        $result = $this->renderer
            ->entry(__DIR__.'/../scripts/app-broken-server.js')
            ->fallback('<div id="app"></div>')
            ->debug(false)
            ->render();

        $this->assertEquals(
            '<div id="app"></div>',
            $result
        );
    }

    /** @test */
    public function it_throws_an_engine_error_when_server_rendering_fails_and_debug_is_enabled()
    {
        $this->expectException(ProcessFailedException::class);

        $this->renderer
            ->entry(__DIR__.'/../scripts/app-broken-server.js')
            ->debug()
            ->render();
    }

    /** @test */
    public function it_always_throws_an_exception_when_the_server_script_does_not_exist()
    {
        $this->expectException(ServerScriptNotReadable::class);

        $this->renderer
            ->entry(__DIR__.'/../scripts/app-doesnt-exist.js')
            ->debug(false)
            ->render();
    }

    /** @test */
    public function it_can_register_an_entry_resolver()
    {
        $result = $this->renderer
            ->resolveEntryWith(function (string $identifier) {
                return __DIR__."/../scripts/{$identifier}-server.js";
            })
            ->entry('app')
            ->render();

        $this->assertEquals(
            '<p>Hello, world!</p>',
            $result
        );
    }
}
