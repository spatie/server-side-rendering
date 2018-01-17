<?php

namespace Spatie\Ssr\Engines;

use Illuminate\Support\Facades\Storage;
use Spatie\Ssr\Engine;
use Spatie\Ssr\Exceptions\EngineError;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Node implements Engine
{
    /** @var string */
    protected $nodePath;

    /** @var string */
    protected $tempPath;

    public function __construct(string $nodePath, string $tempPath)
    {
        $this->nodePath = $nodePath;
        $this->tempPath = $tempPath;
    }

    /**
     * @param string $script
     *
     * @return string
     */
    public function run(string $script): string
    {
        $tempFilePath = $this->createTempFilePath();

        file_put_contents($tempFilePath, $script);

        $process = new Process([$this->nodePath, $tempFilePath]);

        try {
            return substr($process->mustRun()->getOutput(), 0, -1);
        } catch (ProcessFailedException $exception) {
            throw EngineError::withException($exception);
        } finally {
            unlink($tempFilePath);
        }
    }

    protected function createTempFilePath(): string
    {
        return $this->tempPath . '/' . md5(time()) . '.js';
    }
}
