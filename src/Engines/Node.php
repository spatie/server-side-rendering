<?php

namespace Spatie\Ssr\Engines;

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

    public function run(string $script): string
    {
        $tempFilePath = $this->createTempFilePath();

        file_put_contents($tempFilePath, $script);

        // Version 4.2 introduced fromShellCommandline
        $command = "{$this->nodePath} {$tempFilePath}";
        if (method_exists(Process::class, 'fromShellCommandline')) {
            $process = Process::fromShellCommandline($command);
        } else {
            $process = new Process($command);
        }

        try {
            return substr($process->mustRun()->getOutput(), 0, -1);
        } catch (ProcessFailedException $exception) {
            throw EngineError::withException($exception);
        } finally {
            unlink($tempFilePath);
        }
    }

    public function getDispatchHandler(): string
    {
        return 'console.log';
    }

    protected function createTempFilePath(): string
    {
        return implode(DIRECTORY_SEPARATOR, [$this->tempPath, md5(intval(microtime(true) * 1000).random_bytes(5)).'.js']);
    }
}
