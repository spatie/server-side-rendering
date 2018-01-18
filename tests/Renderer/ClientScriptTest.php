<?php

namespace Spatie\Ssr\Tests\Renderer;

class ClientScriptTest extends TestCase
{
    /** @test */
    public function it_can_render_an_app_without_a_client_script_tag()
    {
        $result = $this->renderer
            ->entry('app')
            ->withoutScript()
            ->render();

        $this->assertEquals(
            '<p>Hello, world!</p>',
            $result
        );
    }

    /** @test */
    public function it_can_render_an_app_with_a_client_script_tag()
    {
        $result = $this->renderer
            ->entry('app')
            ->withoutScript()
            ->withScript()
            ->render();

        $this->assertEquals(
            '<p>Hello, world!</p><script src="/js/app-client.js"></script>',
            $result
        );
    }

    /** @test */
    public function it_can_render_the_client_script_tag_with_defer()
    {
        $result = $this->renderer
            ->entry('app')
            ->loadScriptDeferred()
            ->render();

        $this->assertEquals(
            '<p>Hello, world!</p><script defer src="/js/app-client.js"></script>',
            $result
        );
    }

    /** @test */
    public function it_can_render_the_client_script_tag_with_async()
    {
        $result = $this->renderer
            ->entry('app')
            ->loadScriptAsync()
            ->render();

        $this->assertEquals(
            '<p>Hello, world!</p><script async src="/js/app-client.js"></script>',
            $result
        );
    }

    /** @test */
    public function it_can_render_the_client_script_tag_in_sync()
    {
        $result = $this->renderer
            ->entry('app')
            ->loadScriptAsync()
            ->loadScriptSync()
            ->render();

        $this->assertEquals(
            '<p>Hello, world!</p><script src="/js/app-client.js"></script>',
            $result
        );
    }

    /** @test */
    public function it_can_render_the_client_script_tag_before_the_ssr_output()
    {
        $result = $this->renderer
            ->entry('app')
            ->loadScriptBefore()
            ->render();

        $this->assertEquals(
            '<script src="/js/app-client.js"></script><p>Hello, world!</p>',
            $result
        );
    }

    /** @test */
    public function it_can_render_the_client_script_tag_after_the_ssr_output()
    {
        $result = $this->renderer
            ->entry('app')
            ->loadScriptBefore()
            ->loadScriptAfter()
            ->render();

        $this->assertEquals(
            '<p>Hello, world!</p><script src="/js/app-client.js"></script>',
            $result
        );
    }
}
