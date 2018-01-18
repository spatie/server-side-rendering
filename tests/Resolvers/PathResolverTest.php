<?php

namespace Spatie\Ssr\Tests\Resolvers;

use PHPUnit\Framework\TestCase;
use Spatie\Ssr\Resolvers\PathResolver;

class PathResolverTest extends TestCase
{
    /** @var string */
    private $scriptsPath, $publicPath;

    public function setUp()
    {
        $this->scriptsPath = __DIR__.'/../scripts';
        $this->publicPath = '/js';
    }

    /** @test */
    public function it_can_resolve_a_client_scripts_url()
    {
        $resolver = new PathResolver($this->scriptsPath, $this->publicPath);

        $clientScriptUrl = $resolver->getClientScriptUrl('app');

        $this->assertEquals('/js/app-client.js', $clientScriptUrl);
    }

    /** @test */
    public function it_can_resolve_a_server_scripts_contents()
    {
        $resolver = new PathResolver($this->scriptsPath, $this->publicPath);

        $serverScript = $resolver->getServerScriptContents('app');

        $this->assertEquals("dispatch('<p>Hello, world!</p>');\n", $serverScript);
    }
}
