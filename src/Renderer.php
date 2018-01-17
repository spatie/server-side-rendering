<?php

namespace Spatie\Ssr;

use Exception;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\Ssr\Exceptions\EngineError;

class Renderer implements Htmlable
{
    /** @var \Spatie\ServerRenderer\Engine */
    protected $engine;

    /** @var \Spatie\ServerRenderer\Resolver */
    protected $resolver;

    /** @var array */
    protected $context;

    /** @var bool */
    protected $enabled = true;

    /** @var string */
    protected $entry;

    /** @var string */
    protected $fallback;

    /** @var bool */
    protected $withScript = false;

    /** @var string */
    protected $scriptLoadStrategy;

    /** @var string */
    protected $scriptPosition = 'after';

    /** @var bool */
    protected $debug = false;

    public function __construct(Engine $engine, Resolver $resolver) {
        $this->engine = $engine;
        $this->resolver = $resolver;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function enabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @param bool $debug
     *
     * @return $this
     */
    public function debug(bool $debug)
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
        if (! is_array($context)) {
            $context = [$context => $value];
        }

        foreach ($context as $key => $value) {
            $this->context[$key] = $value;
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
            $result = $this->engine->run([
                $this->envScript(),
                $this->appScript(),
            ]);
        } catch (EngineError $exception) {
            if ($this->debug) {
                throw $exception->getException();
            }

            return $this->renderFallback();
        }

        if ($this->scriptPosition === 'before') {
            return $this->contextTag() . $this->scriptTag() . $result;
        }

        return $this->contextTag() . $result . $this->scriptTag();
    }

    public function toHtml(): string
    {
        return $this->render();
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

    protected function envScript(): string
    {
        $process = json_encode(['env' => ['VUE_ENV' => 'server', 'NODE_ENV' => 'production']]);
        $context = json_encode($this->context, JSON_FORCE_OBJECT);

        return "var process = {$process}; var ssrContext = {$context};";
    }

    protected function appScript(): string
    {
        return $this->resolver->getServerScript($this->entry);
    }

    protected function contextTag(): string
    {
        return '<script>var ssrContext = ' . json_encode($this->context, JSON_FORCE_OBJECT) . ';</script>';
    }

    protected function scriptTag(): string
    {
        $clientScriptUrl = $this->resolver->getClientScriptUrl($this->entry);

        $scriptLoadStrategy = $this->scriptLoadStrategy ? " {$this->scriptLoadStrategy}" : '';

        return "<script{$scriptLoadStrategy} src=\"{$clientScriptUrl}\"></script>";
    }
}
