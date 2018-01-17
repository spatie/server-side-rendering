<?php

namespace Spatie\Ssr\Engines;

use V8Js;
use V8JsException;
use Spatie\Ssr\Engine;
use Spatie\Ssr\Exceptions\EngineError;

class V8 implements Engine
{
    /** @var \V8Js */
    protected $v8;

    public function __construct()
    {
        $this->v8 = new V8Js();
    }

    /**
     * @param string $script
     *
     * @return string
     */
    public function run(string $script): string
    {
        try {
            ob_start();

            $this->v8->executeString($script);

            return ob_get_contents();
        } catch (V8JsException $exception) {
            throw EngineError::withException($exception);
        } finally {
            ob_end_clean();
        }
    }
}
