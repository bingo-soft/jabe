<?php

namespace Jabe\Engine\Impl\Scripting\Engine;

use Jabe\Engine\Impl\Util\Scripting\{
    ScriptEngineInterface,
    ScriptEngineFactoryInterface,
    ScriptEngineManager
};

interface ScriptEngineResolverInterface
{
    public function addScriptEngineFactory(ScriptEngineFactoryInterface $scriptEngineFactory): void;

    public function getScriptEngineManager(): ScriptEngineManager;

    /**
     * Returns a cached script engine or creates a new script engine if no such engine is currently cached.
     *
     * @param language the language (such as 'groovy' for the script engine)
     * @return the cached engine or null if no script engine can be created for the given language
     */
    public function getScriptEngine(string $language, bool $resolveFromCache = false): ?ScriptEngineInterface;
}
