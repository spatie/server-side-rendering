<?php

namespace Spatie\Ssr;

use Spatie\Ssr\Exceptions\EngineError;
use Spatie\Ssr\Exceptions\ServerScriptDoesNotExist;

class Renderer
{
    /** @var \Spatie\Ssr\Engine */
    protected $engine;

    /** @var array */
    protected $context = [];

    /** @var array */
    protected $env = [];

    /** @var bool */
    protected $enabled = true;

    /** @var string */
    protected $entry;

    /** @var string */
    protected $fallback = '';

    /** @var bool */
    protected $debug = false;

    /** @var callable|null */
    protected $entryResolver;

    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
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
     * @param bool $enabled
     *
     * @return $this
     */
    public function disabled(bool $disabled = true)
    {
        $this->enabled = ! $disabled;

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
    public function entry(string $entry)
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
    public function context($context, $value = null)
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
     * @param string|array $key
     * @param mixed $value
     *
     * @return $this
     */
    public function env($env, $value = null)
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
    public function fallback(string $fallback)
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * @param callable $resolver
     *
     * @return $this
     */
    public function resolveEntryWith(callable $entryResolver)
    {
        $this->entryResolver = $entryResolver;

        return $this;
    }

    public function render()
    {
        if (! $this->enabled) {
            return $this->fallback;
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

            return $this->fallback;
        }

        $decoded = json_decode($result, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Looks like the engine returned a JSON object.
            return $decoded;
        }

        // Looks like the engine returned a string.
        return $result;
    }

    protected function environmentScript(): string
    {
        $context = empty($this->context) ? '{}' : json_encode($this->context);

        $envAssignments = array_map(function ($value, $key) {
            return "process.env.{$key} = ".json_encode($value);
        }, $this->env, array_keys($this->env));

        return implode(';', [
            'var process = process || { env: {} }',
            implode(';', $envAssignments),
            "var context = {$context}",
        ]);
    }

    protected function dispatchScript(): string
    {
        return <<<JS
var dispatch = function (result) {
    return {$this->engine->getDispatchHandler()}(JSON.stringify(result))
}
JS;
    }

    protected function applicationScript(): string
    {
        $entry = $this->entryResolver
            ? call_user_func($this->entryResolver, $this->entry)
            : $this->entry;

        if (! file_exists($entry)) {
            throw ServerScriptDoesNotExist::atPath($entry);
        }

        return file_get_contents($entry);
    }
}
