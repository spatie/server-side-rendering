<?php

namespace Spatie\Ssr\Tests\Renderer;

class RendererTest extends TestCase
{
    /** @test */
    public function it_can_render_a_javascript_app()
    {
        $result = $this->renderer->withEntry('app')->render();

        $this->assertEquals(
            '<p>Hello, world!</p><script src="/js/app-client.js"></script>',
            $result
        );
    }
}
