<?php

namespace BpmPlatform\Engine\Impl\Scripting\Engine;

use BpmPlatform\Engine\Impl\Util\Scripting\{
    ScriptContextInterface,
    ScriptEngineInterface,
    ScriptEngineFactoryInterface,
    ScriptEngineManager
};
use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use BpmPlatform\Engine\Impl\Context\Context;

class DefaultScriptEngineResolver implements ScriptEngineResolverInterface
{
    protected $scriptEngineManager;

    protected $cachedEngines = [];

    public function __construct(ScriptEngineManager $scriptEngineManager)
    {
        $this->scriptEngineManager = $scriptEngineManager;
    }

    public function addScriptEngineFactory(ScriptEngineFactoryInterface $scriptEngineFactory): void
    {
        $this->scriptEngineManager->registerEngineName($scriptEngineFactory->getEngineName(), $scriptEngineFactory);
    }

    public function getScriptEngineManager(): ScriptEngineManager
    {
        return $this->scriptEngineManager;
    }

    /**
     * Returns a cached script engine or creates a new script engine if no such engine is currently cached.
     *
     * @param language the language (such as 'groovy' for the script engine)
     * @return the cached engine or null if no script engine can be created for the given language
     */
    public function getScriptEngine(string $language, bool $resolveFromCache = false): ?ScriptEngineInterface
    {
        $scriptEngine = null;

        if ($resolveFromCache) {
            $scriptEngine = null;

            if (array_key_exists($language, $this->cachedEngines)) {
                $scriptEngine = $this->cachedEngines[$language];
            }

            if ($scriptEngine == null) {
                $scriptEngine = $this->scriptEngineManager->getEngineByName($language);

                if ($scriptEngine != null) {
                    if ($this->isCachable($scriptEngine)) {
                        $this->cachedEngines[$language] = $scriptEngine;
                    }
                }
            }
        } else {
            $scriptEngine = $this->scriptEngineManager->getEngineByName($language);
        }

        return $scriptEngine;
    }

    /**
     * Allows checking whether the script engine can be cached.
     *
     * @param scriptEngine the script engine to check.
     * @return true if the script engine may be cached.
     */
    protected function isCachable(ScriptEngineInterface $scriptEngine): bool
    {
        // Check if script-engine supports multithreading. If true it can be cached.
        //Object threadingParameter = scriptEngine.getFactory().getParameter("THREADING");
        return true;
    }
}
