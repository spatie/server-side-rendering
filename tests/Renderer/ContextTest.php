<?php

namespace Spatie\Ssr\Tests\Renderer;

class ContextTest extends TestCase
{
    /** @test */
    public function it_can_render_an_app_with_a_context_value()
    {
        $result = $this->renderer
            ->entry(__DIR__.'/../scripts/app-with-context-server.js')
            ->context('user', ['name' => 'Sebastian'])
            ->render();

        $this->assertEquals(
            '<p>Hello, Sebastian!</p>',
            $result
        );
    }

    /** @test */
    public function it_can_render_an_app_with_a_context_array()
    {
        $result = $this->renderer
            ->entry(__DIR__.'/../scripts/app-with-context-server.js')
            ->context(['user' => ['name' => 'Sebastian']])
            ->render();

        $this->assertEquals(
            '<p>Hello, Sebastian!</p>',
            $result
        );
    }
}
