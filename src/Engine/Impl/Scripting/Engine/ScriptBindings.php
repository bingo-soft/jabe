<?php

namespace Jabe\Engine\Impl\Scripting\Engine;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Util\Scripting\{
    BindingsInterface,
    ScriptEngineInterface
};

class ScriptBindings implements BindingsInterface
{
    /**
     * The script engine implementations put some key/value pairs into the binding.
     * This list contains those keys, such that they wouldn't be stored as process variable.
     *
     * This list contains the keywords for JUEL, Javascript and Groovy.
     */
    protected static $UNSTORED_KEYS = [
        "out",
        "out:print",
        "lang:import",
        "context",
        "elcontext",
        "print",
        "println",
        "S", // Spin Internal Variable
        "XML", // Spin Internal Variable
        "JSON", // Spin Internal Variable
        ScriptEngineInterface::ARGV,
        "execution",
        "__doc__" // do not export python doc string
    ];

    protected $scriptResolvers;
    protected $variableScope;

    protected $wrappedBindings;

    /** if true, all script variables will be set in the variable scope. */
    protected $autoStoreScriptVariables;

    public function __construct(array $scriptResolvers, VariableScopeInterface $variableScope, BindingsInterface $wrappedBindings)
    {
        $this->scriptResolvers = $scriptResolvers;
        $this->variableScope = $variableScope;
        $this->wrappedBindings = $wrappedBindings;
        $this->autoStoreScriptVariables = $this->isAutoStoreScriptVariablesEnabled();
    }

    protected function isAutoStoreScriptVariablesEnabled(): bool
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        if ($processEngineConfiguration != null) {
            return $processEngineConfiguration->isAutoStoreScriptVariables();
        }
        return false;
    }

    public function containsKey($key): bool
    {
        foreach ($this->scriptResolvers as $scriptResolver) {
            if ($scriptResolver->containsKey($key)) {
                return true;
            }
        }
        return $this->wrappedBindings->containsKey($key);
    }

    public function get($key)
    {
        $result = null;

        if ($this->wrappedBindings->containsKey($key)) {
            $result = $this->wrappedBindings->get($key);
        } else {
            foreach ($this->scriptResolvers as $scriptResolver) {
                if ($scriptResolver->containsKey($key)) {
                    $result = $scriptResolver->get($key);
                }
            }
        }

        return $result;
    }

    public function put(string $name, $value)
    {

        if ($this->autoStoreScriptVariables) {
            if (!self::in_array($name, self::$UNSTORED_KEYS)) {
                $oldValue = $this->variableScope->getVariable($name);
                $this->variableScope->setVariable($name, $value);
                return $oldValue;
            }
        }

        return $this->wrappedBindings->put($name, $alue);
    }

    public function entrySet(): array
    {
        return $this->calculateBindingMap();
    }

    public function keySet(): array
    {
        return array_keys($this->calculateBindingMap());
    }

    public function size(): int
    {
        return count($this->calculateBindingMap());
    }

    public function values(): array
    {
        return array_values($this->calculateBindingMap());
    }

    public function putAll(array $toMerge): void
    {
        foreach ($toMerge as $key => $value) {
            $this->put($key, $value);
        }
    }

    public function remove($key)
    {
        if (in_array($key, self::$UNSTORED_KEYS)) {
            return null;
        }
        return $this->wrappedBindings->remove($key);
    }

    public function clear(): void
    {
        $this->wrappedBindings->clear();
    }

    public function containsValue($value): bool
    {
        return in_array($value, $this->calculateBindingMap());
    }

    public function isEmpty(): bool
    {
        return empty($this->calculateBindingMap());
    }

    protected function calculateBindingMap(): array
    {
        $bindingMap = [];

        foreach ($this->scriptResolvers as $resolver) {
            foreach ($resolver->keySet() as $key) {
                $bindingMap[$key] = $resolver->get($key);
            }
        }

        $wrappedBindingsEntries = $this->wrappedBindings->entrySet();
        foreach ($wrappedBindingsEntries as $key => $value) {
            $bindingMap[$key] = $value;
        }

        return $bindingMap;
    }
}
