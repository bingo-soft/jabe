<?php

namespace Jabe\Impl\Core\Variable\Scope;

use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Core\Variable\CoreVariableInstanceInterface;

class VariableStore
{
    protected $variablesProvider;
    protected $variables;

    protected $removedVariables = [];

    protected $observers = [];

    public function __construct(?VariablesProviderInterface $provider = null, VariableStoreObserverInterface ...$observers)
    {
        $this->variablesProvider = $provider ?? VariableCollectionProvider::emptyVariables();
        $this->observers = empty($observers) ? [] : $observers;
    }

    /**
     * The variables provider can be exchanged as long as the variables are not yet initialized
     */
    public function setVariablesProvider(VariablesProviderInterface $variablesProvider): void
    {
        if ($this->variables === null) {
            $this->variablesProvider = $variablesProvider;
        }
    }

    protected function &getVariablesMap(?array $variableNames = []): array
    {
        if (empty($variableNames)) {
            $this->forceInitialization();
            return $this->variables;
        }
        $result = [];

        if ($this->isInitialized()) {
            foreach ($variableNames as $variableName) {
                if (array_key_exists($variableName, $this->variables)) {
                    $result[$variableName] = $this->variables[$variableName];
                }
            }
        } else {
            // in this case we don't initialize the variables map,
            // otherwise it would most likely contain only a subset
            // of existing variables
            foreach ($this->variablesProvider->provideVariables($variableNames) as $variable) {
                $result[$variable->getName()] = $variable;
            }
        }

        return $result;
    }

    public function getRemovedVariable(?string $name): ?CoreVariableInstanceInterface
    {
        if (array_key_exists($name, $this->removedVariables)) {
            return $this->removedVariables[$name];
        }
        return null;
    }

    public function getVariable(?string $name): ?CoreVariableInstanceInterface
    {
        $variablesMap = $this->getVariablesMap();
        if (array_key_exists($name, $variablesMap)) {
            return $variablesMap[$name];
        }
        return null;
    }

    public function getVariables(?array $variableNames = []): array
    {
        return array_values($this->getVariablesMap($variableNames));
    }

    public function addVariable(CoreVariableInstanceInterface $value): void
    {
        if ($this->containsKey($value->getName())) {
            //throw ProcessEngineLogger.CORE_LOGGER.duplicateVariableInstanceException(value);
        } else {
            $map = &$this->getVariablesMap();
            $map[$value->getName()] = $value;
            foreach ($this->observers as $listener) {
                $listener->onAdd($value);
            }

            if (array_key_exists($value->getName(), $this->removedVariables)) {
                unset($this->removedVariables[$value->getName()]);
            }
        }
    }

    public function updateVariable(CoreVariableInstanceInterface $value): void
    {
        if (!$this->containsKey($value->getName())) {
            //throw ProcessEngineLogger.CORE_LOGGER.duplicateVariableInstanceException(value);
        } else {
            $this->variables[$value->getName()] = $value;
        }
    }

    public function isEmpty(): bool
    {
        $this->getVariablesMap();
        return empty($this->variables);
    }

    public function containsValue(CoreVariableInstanceInterface $value): bool
    {
        $this->getVariablesMap();
        foreach ($this->variables as $variable) {
            if ($variable == $value) {
                return true;
            }
        }
        return false;
    }

    public function containsKey(?string $key): bool
    {
        $this->getVariablesMap();
        return array_key_exists($key, $this->variables);
    }

    public function getKeys(): array
    {
        $this->getVariablesMap();
        return array_keys($this->variables);
    }

    public function isInitialized(): bool
    {
        return $this->variables !== null;
    }

    public function forceInitialization(): void
    {
        if (!$this->isInitialized()) {
            $this->variables = [];
            foreach ($this->variablesProvider->provideVariables() as $variable) {
                $this->variables[$variable->getName()] = $variable;
            }
        }
    }

    public function removeVariable(?string $variableName): ?CoreVariableInstanceInterface
    {
        if (!$this->containsKey($variableName)) {
            return null;
        }

        $value = $this->variables[$variableName];
        unset($this->variables[$variableName]);

        foreach ($this->observers as $observer) {
            $observer->onRemove($value);
        }

        $this->removedVariables[$variableName] = $value;

        return $value;
    }

    public function removeVariables(): void
    {
        foreach ($this->getVariables() as $variable) {
            $this->removeVariable($variable->getName());
        }
    }

    public function addObserver(VariableStoreObserverInterface $observer): void
    {
        $this->observers[] = $observer;
    }

    public function removeObserver(VariableStoreObserverInterface $observer): void
    {
        foreach ($this->observers as $key => $curObserver) {
            if ($curObserver == $observer) {
                unset($this->observers[$key]);
            }
        }
    }

    public function isRemoved(?string $variableName): bool
    {
        return array_key_exists($variableName, $this->removedVariables);
    }
}
