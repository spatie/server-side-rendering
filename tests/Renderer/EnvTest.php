<?php

namespace Spatie\Ssr\Tests\Renderer;

class EnvTest extends TestCase
{
    /** @test */
    public function it_can_render_an_app_with_an_env_value()
    {
        $result = $this->renderer
            ->entry(__DIR__.'/../scripts/app-with-env-server.js')
            ->env('APP_ENV', 'production')
            ->render();

        $this->assertEquals(
            '<p>Hello, world! Rendered in production.</p>',
            $result
        );
    }

    /** @test */
    public function it_can_render_an_app_with_an_env_array()
    {
        $result = $this->renderer
            ->entry(__DIR__.'/../scripts/app-with-env-server.js')
            ->env(['APP_ENV' => 'production'])
            ->render();

        $this->assertEquals(
            '<p>Hello, world! Rendered in production.</p>',
            $result
        );
    }
}
