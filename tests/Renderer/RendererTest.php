<?php

namespace Spatie\Ssr\Tests\Renderer;

use Symfony\Component\Process\Exception\ProcessFailedException;

class RendererTest extends TestCase
{
    /** @test */
    public function it_can_render_a_javascript_app()
    {
        $result = $this->renderer
            ->entry('app')
            ->render();

        $this->assertEquals(
            '<p>Hello, world!</p><script src="/js/app-client.js"></script>',
            $result
        );
    }

    /** @test */
    public function it_renders_a_fallback_when_disabled()
    {
        $result = $this->renderer
            ->entry('app')
            ->withFallback('<div id="app"></div>')
            ->enabled(false)
            ->render();

        $this->assertEquals(
            '<div id="app"></div><script src="/js/app-client.js"></script>',
            $result
        );
    }

    /** @test */
    public function it_renders_a_fallback_when_server_rendering_fails()
    {
        $result = $this->renderer
            ->entry('app-broken')
            ->withFallback('<div id="app"></div>')
            ->render();

        $this->assertEquals(
            '<div id="app"></div><script src="/js/app-broken-client.js"></script>',
            $result
        );
    }

    /** @test */
    public function it_throws_an_engine_error_when_server_rendering_fails_and_debug_enabled()
    {
        $this->expectException(ProcessFailedException::class);

        $this->renderer
            ->entry('app-broken')
            ->debug()
            ->render();
    }

    /** @test */
    public function it_implements_to_string()
    {
        $result = (string) $this->renderer->entry('app');

        $this->assertEquals(
            '<p>Hello, world!</p><script src="/js/app-client.js"></script>',
            $result
        );
    }
}
