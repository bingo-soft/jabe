<?php

namespace Jabe\Engine\Impl\Scripting\Env;

use Jabe\Engine\Application\{
    AbstractProcessApplication,
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface,
    ProcessApplicationUnavailableException
};
use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Scripting\{
    ExecutableScript,
    ScriptFactory
};
use Jabe\Engine\Impl\Scripting\Engine\ScriptingEngines;
use Jabe\Engine\Impl\Util\Scripting\{
    BindingsInterface,
    ScriptEngineInterface
};

class ScriptingEnvironment
{
    /** the cached environment scripts per script language */
    protected $env = [];

    /** the resolvers */
    protected $envResolvers = [];

    /** the script factory used for compiling env scripts */
    protected $scriptFactory;

    /** the scripting engines */
    protected $scriptingEngines;

    public function __construct(ScriptFactory $scriptFactory, array $scriptEnvResolvers, ScriptingEngines $scriptingEngines)
    {
        $this->scriptFactory = $scriptFactory;
        $this->envResolvers = $scriptEnvResolvers;
        $this->scriptingEngines = $scriptingEngines;
    }

    public function execute(ExecutableScript $script, VariableScopeInterface $scope, ?BindingsInterface $bindings = null, ?ScriptEngineInterface $scriptEngine = null)
    {
        if ($scriptEngine == null) {
            $scriptEngine = $this->scriptingEngines->getScriptEngineForLanguage($script->getLanguage());
        }
        if ($bindings == null) {
            $bindings = $this->scriptingEngines->createBindings($scriptEngine, $scope);
        }
        // first, evaluate the env scripts (if any)
        $envScripts = $this->getEnvScripts($script, $scriptEngine);
        foreach ($envScripts as $envScript) {
            $envScript->execute($scriptEngine, $scope, $bindings);
        }

        // next evaluate the actual script
        return $script->execute($scriptEngine, $scope, $bindings);
    }

    protected function getEnv(string $language): array
    {
        $config = Context::getProcessEngineConfiguration();
        $processApplication = Context::getCurrentProcessApplication();

        $result = null;
        if ($config->isEnableFetchScriptEngineFromProcessApplication()) {
            if ($processApplication != null) {
                $result = $this->getPaEnvScripts($processApplication);
            }
        }

        return $result != null ? $result : $this->env;
    }

    protected function getPaEnvScripts(ProcessApplicationReferenceInterface $pa): array
    {
        try {
            $processApplication = $pa->getProcessApplication();
            $rawObject = $processApplication->getRawObject();

            if ($rawObject instanceof AbstractProcessApplication) {
                $abstractProcessApplication = $rawObject;
                return $abstractProcessApplication->getEnvironmentScripts();
            }
            return null;
        } catch (ProcessApplicationUnavailableException $e) {
            throw new ProcessEngineException("Process Application is unavailable.", $e);
        }
    }

    protected function getEnvScripts($scriptOrLang, ?ScriptEngineInterface $scriptEngine = null): array
    {
        if ($scriptOrLang instanceof ExecutableScript) {
            $envScripts = $this->getEnvScripts(strtolower($script->getLanguage()));
            if (empty($envScripts)) {
                $envScripts = $this->getEnvScripts(strtolower($scriptEngine->getFactory()->getLanguageName()));
            }
            return $envScripts;
        } elseif (is_string($scriptOrLang)) {
            $environment = $this->getEnv($scriptOrLang);
            $envScripts = $environment->get($scriptOrLang);
            if ($envScripts == null) {
                $envScripts = $this->initEnvForLanguage($scriptOrLang);
                $environment->put($scriptOrLang, $envScripts);
            }
            return $envScripts;
        }
        return [];
    }

    /**
     * Initializes the env scripts for a given language.
     *
     * @param language the language
     * @return the list of env scripts. Never null.
     */
    protected function initEnvForLanguage(string $language): array
    {
        $scripts = [];
        foreach ($envResolvers as $resolver) {
            $resolvedScripts = $resolver->resolve($language);
            if ($resolvedScripts != null) {
                foreach ($resolvedScripts as $resolvedScript) {
                    $scripts[] = $scriptFactory->createScriptFromSource($language, $resolvedScript);
                }
            }
        }

        return $scripts;
    }
}
