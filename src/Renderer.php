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
    public function withScript()
    {
        $this->withScript = true;

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
    public function loadScriptBefore()
    {
        $this->scriptPosition = 'before';

        return $this;
    }

    /**
     * @return $this
     */
    public function loadScriptAfter()
    {
        $this->scriptPosition = 'after';

        return $this;
    }

    /**
     * @return $this
     */
    public function loadScriptDeferred()
    {
        $this->scriptLoadStrategy = 'defer';

        return $this;
    }

    /**
     * @return $this
     */
    public function loadScriptAsync()
    {
        $this->scriptLoadStrategy = 'async';

        return $this;
    }

    /**
     * @return $this
     */
    public function loadScriptSync()
    {
        $this->scriptLoadStrategy = null;

        return $this;
    }

    public function render(): string
    {
        if (! $this->enabled) {
            return $this->renderWithOutput($this->fallback);
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

            return $this->renderWithOutput($this->fallback);
        }

        return $this->renderWithOutput($result);
    }

    public function __toString() : string
    {
        return $this->render();
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

    protected function renderWithOutput(string $output): string
    {
        if (! $this->withScript) {
            return $output;
        }

        $clientScriptUrl = $this->resolver->getClientScriptUrl($this->entry);

        $scriptLoadStrategy = $this->scriptLoadStrategy ? " {$this->scriptLoadStrategy}" : '';

        $scriptTag = "<script{$scriptLoadStrategy} src=\"{$clientScriptUrl}\"></script>";

        if ($this->scriptPosition === 'before') {
            return $scriptTag . $output;
        }

        return $output . $scriptTag;
    }
}
