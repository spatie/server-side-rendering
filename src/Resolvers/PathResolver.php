<?php

namespace Spatie\Ssr\Resolvers;

use Spatie\Ssr\Resolver;

class PathResolver implements Resolver
{
    /** @var string */
    protected $rootPath;

    /** @var string */
    protected $publicPath;

    public function __construct(string $rootPath, string $publicPath)
    {
        $this->rootPath = $rootPath;
        $this->publicPath = $publicPath;
    }

    public function getClientScriptUrl(string $identifier): string
    {
        return $this->publicPath.'/'.trim($identifier, '/').'-client.js';
    }

    public function getServerScriptContents(string $identifier): string
    {
        $path = $this->rootPath.'/'.trim($identifier, '/').'-server.js';

        return file_get_contents($path);
    }
}
