<?php

namespace Jabe\Impl\Scripting\Engine;

use Script\{
    BindingsInterface,
    ScriptEngineInterface,
    ScriptEngineFactoryInterface,
    ScriptEngineManager
};
use Jabe\Application\{
    AbstractProcessApplication,
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface,
    ProcessApplicationUnavailableException
};
//use JabeDmn\Impl\Spi\El\DmnScriptEngineResolverInterface;
use Jabe\ProcessEngineException;
use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Context\Context;
use Script\ScriptEngineResolverInterface;

class ScriptingEngines
{
    public const DEFAULT_SCRIPTING_LANGUAGE = "juel";

    protected $scriptEngineResolver;
    protected $scriptBindingsFactory;

    protected bool $enableScriptEngineCaching = true;

    public function __construct(ScriptEngineResolverInterface $scriptEngineResolver, ?ScriptBindingsFactory $scriptBindingsFactory = null)
    {
        $this->scriptEngineResolver = $scriptEngineResolver;
        $this->scriptBindingsFactory = $scriptBindingsFactory;
    }

    public function isEnableScriptEngineCaching(): bool
    {
        return $this->enableScriptEngineCaching;
    }

    public function setEnableScriptEngineCaching(bool $enableScriptEngineCaching): void
    {
        $this->enableScriptEngineCaching = $enableScriptEngineCaching;
    }

    public function getScriptEngineManager(): ScriptEngineManager
    {
        return $this->scriptEngineResolver->getScriptEngineManager();
    }

    public function addScriptEngineFactory(ScriptEngineFactoryInterface $scriptEngineFactory): ScriptingEngines
    {
        $this->scriptEngineResolver->addScriptEngineFactory($scriptEngineFactory);
        return $this;
    }

    /**
     * Loads the given script engine by language name. Will throw an exception if no script engine can be loaded for the given language name.
     *
     * @param language the name of the script language to lookup an implementation for
     * @return ScriptEngineInterface the script engine
     * @throws ProcessEngineException if no such engine can be found.
     */
    public function getScriptEngineForLanguage(?string $language): ScriptEngineInterface
    {
        $language = strtolower($language);

        $pa = Context::getCurrentProcessApplication();
        $config = Context::getProcessEngineConfiguration();

        $engine = null;
        if ($config->isEnableFetchScriptEngineFromProcessApplication()) {
            if ($pa !== null) {
                $engine = $this->getPaScriptEngine($language, $pa);
            }
        }

        if ($engine === null) {
            $engine = $this->getGlobalScriptEngine($language);
        }

        return $engine;
    }

    protected function getPaScriptEngine(?string $language, ProcessApplicationReferenceInterface $pa): ?ScriptEngineInterface
    {
        try {
            $processApplication = $pa->getProcessApplication();
            $rawObject = $processApplication->getRawObject();

            if ($rawObject instanceof AbstractProcessApplication) {
                $abstractProcessApplication = $rawObject;
                return $abstractProcessApplication->getScriptEngineForName($language, $this->enableScriptEngineCaching);
            }
            return null;
        } catch (ProcessApplicationUnavailableException $e) {
            throw new ProcessEngineException("Process Application is unavailable.", $e);
        }
    }

    protected function getGlobalScriptEngine(?string $language): ScriptEngineInterface
    {
        $scriptEngine = $this->scriptEngineResolver->getScriptEngine($language, $this->enableScriptEngineCaching);
        return $scriptEngine;
    }

    /** override to build a spring aware ScriptingEngines
     * @param engineBindin
     * @param scriptEngine */
    public function createBindings(ScriptEngineInterface $scriptEngine, VariableScopeInterface $variableScope): BindingsInterface
    {
        return $this->scriptBindingsFactory->createBindings($variableScope, $scriptEngine->createBindings());
    }

    public function getScriptBindingsFactory(): ScriptBindingsFactory
    {
        return $this->scriptBindingsFactory;
    }

    public function setScriptBindingsFactory(ScriptBindingsFactory $scriptBindingsFactory): void
    {
        $this->scriptBindingsFactory = $scriptBindingsFactory;
    }

    public function setScriptEngineResolver(ScriptEngineResolverInterface $scriptEngineResolver): void
    {
        $this->scriptEngineResolver = $scriptEngineResolver;
    }
}
