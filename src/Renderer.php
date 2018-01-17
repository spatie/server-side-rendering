<?php

namespace Spatie\Ssr;

use Exception;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\Ssr\Exceptions\EngineError;

class Renderer
{
    /** @var \Spatie\ServerRenderer\Engine */
    protected $engine;

    /** @var \Spatie\ServerRenderer\Resolver */
    protected $resolver;

    /** @var array */
    protected $context = [];

    /** @var array */
    protected $env = [];

    /** @var bool */
    protected $enabled = true;

    /** @var string */
    protected $entry;

    /** @var string */
    protected $fallback;

    /** @var bool */
    protected $withScript = true;

    /** @var string */
    protected $scriptLoadStrategy;

    /** @var string */
    protected $scriptPosition = 'after';

    /** @var bool */
    protected $debug = false;

    public function __construct(Engine $engine, Resolver $resolver)
    {
        $this->engine = $engine;
        $this->resolver = $resolver;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function enabled(bool $enabled = true)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @param bool $debug
     *
     * @return $this
     */
    public function debug(bool $debug = true)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @param string $entry
     *
     * @return $this
     */
    public function withEntry(string $entry)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * @param string|array $key
     * @param mixed $value
     *
     * @return $this
     */
    public function withContext($context, $value = null)
    {
        if (!is_array($context)) {
            $context = [$context => $value];
        }

        foreach ($context as $key => $value) {
            $this->context[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string|array $key
     * @param mixed $value
     *
     * @return $this
     */
    public function withEnv($env, $value = null)
    {
        if (! is_array($env)) {
            $env = [$env => $value];
        }

        foreach ($env as $key => $value) {
            $this->env[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string $fallback
     *
     * @return $this
     */
    public function withFallback(string $fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutScript()
    {
        $this->withScript = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function withScript(string $loadStrategy = null, string $position = 'after')
    {
        if (! in_array($loadStrategy, [null, 'async', 'defer'], true)) {
            throw new Exception();
        }

        if (! in_array($position, ['before', 'after'], true)) {
            throw new Exception();
        }

        $this->withScript = true;
        $this->scriptLoadStrategy = $loadStrategy;
        $this->scriptPosition = $position;

        return $this;
    }

    public function render(): string
    {
        if (! $this->enabled) {
            return $this->renderFallback();
        }

        try {
            $serverScript = implode(';', [
                $this->dispatchScript(),
                $this->environmentScript(),
                $this->applicationScript(),
            ]);

            $result = $this->engine->run($serverScript);
        } catch (EngineError $exception) {
            if ($this->debug) {
                throw $exception->getException();
            }

            return $this->renderFallback();
        }

        if ($this->scriptPosition === 'before') {
            return $this->scriptTag() . $result;
        }

        return $result . $this->scriptTag();
    }

    public function __toString() : string
    {
        return $this->render();
    }

    protected function renderFallback(): string
    {
        if ($this->scriptPosition === 'before') {
            return $this->scriptTag() . $this->fallback;
        }

        return $this->fallback . $this->scriptTag();
    }

    protected function environmentScript(): string
    {
        $context = json_encode($this->context, JSON_FORCE_OBJECT);
        $process = json_encode($this->env, JSON_FORCE_OBJECT);

        $envAssignments = array_map(function ($value, $key) {
            return "process.env.{$key} = " . json_encode($value);
        }, $this->env, array_keys($this->env));

        return implode(';', [
            'var process = process || { env: {} }',
            implode(';', $envAssignments),
            "var context = {$context}",
        ]);
    }

    protected function dispatchScript(): string
    {
        return "var dispatch = {$this->engine->getDispatchHandler()}";
    }

    protected function applicationScript(): string
    {
        return $this->resolver->getServerScriptContents($this->entry);
    }

    protected function scriptTag(): string
    {
        $clientScriptUrl = $this->resolver->getClientScriptUrl($this->entry);

        $scriptLoadStrategy = $this->scriptLoadStrategy ? " {$this->scriptLoadStrategy}" : '';

        return "<script{$scriptLoadStrategy} src=\"{$clientScriptUrl}\"></script>";
    }
}
