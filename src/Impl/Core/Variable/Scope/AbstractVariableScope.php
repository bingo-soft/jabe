<?php

namespace Jabe\Impl\Core\Variable\Scope;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Variable\{
    CoreVariableInstanceInterface,
    SetVariableFunctionInterface,
    VariableUtil
};
use Jabe\Impl\Core\Variable\Event\{
    VariableEvent,
    VariableEventDispatcherInterface
};
use El\ELContext;
use Jabe\Variable\Variables;
use Jabe\Variable\Impl\VariableMapImpl;
use Jabe\Variable\Value\TypedValueInterface;

abstract class AbstractVariableScope implements VariableScopeInterface, VariableEventDispatcherInterface
{
    protected $cachedElContext;
    abstract protected function getVariableStore(): VariableStore;
    abstract protected function getVariableInstanceFactory(): VariableInstanceFactoryInterface;
    abstract protected function getVariableInstanceLifecycleListeners(): array;

    abstract public function getParentVariableScope(): ?AbstractVariableScope;

    public function initializeVariableStore(array $variables): void
    {
        foreach ($variables as $variableName => $value) {
            $typedValue = Variables::untypedValue($value);
            $variableValue = $this->getVariableInstanceFactory()->build($variableName, $typedValue, false);
            $this->getVariableStore()->addVariable($variableValue);
        }
    }

    public function getVariableScopeKey(): ?string
    {
        return "scope";
    }

    public function getVariables(): VariableMapImpl
    {
        return $this->getVariablesTyped();
    }

    public function getVariablesTyped(?bool $deserializeValues = true): VariableMapImpl
    {
        $variableMap = new VariableMapImpl();
        $this->collectVariables($variableMap, null, false, $deserializeValues);
        return $variableMap;
    }

    public function getVariablesLocal(): VariableMapImpl
    {
        return $this->getVariablesLocalTyped();
    }

    public function getVariablesLocalTyped(?bool $deserializeObjectValues = true): VariableMapImpl
    {
        $variables = new VariableMapImpl();
        $this->collectVariables($variables, null, true, $deserializeObjectValues);
        return $variables;
    }

    public function collectVariables(VariableMapImpl $resultVariables, ?array $variableNames = [], bool $isLocal = false, bool $deserializeValues = false): void
    {
        $collectAll = empty($variableNames);

        $localVariables = $this->getVariableInstancesLocal($variableNames);
        foreach ($localVariables as $var) {
            if (
                !$resultVariables->containsKey($var->getName()) &&
                ($collectAll || in_array($var->getName(), $variableNames))
            ) {
                $resultVariables->put($var->getName(), $var->getTypedValue($deserializeValues));
            }
        }
        if (!$isLocal) {
            $parentScope = $this->getParentVariableScope();
            // Do not propagate to parent if all variables in 'variableNames' are already collected!
            if ($parentScope !== null && ($collectAll || array_keys($resultVariables) != $variableNames)) {
                $parentScope->collectVariables($resultVariables, $variableNames, $isLocal, $deserializeValues);
            }
        }
    }

    public function getVariable(?string $variableName, ?bool $deserializeObjectValue = true)
    {
        return $this->getValueFromVariableInstance($deserializeObjectValue, $this->getVariableInstance($variableName));
    }

    public function getVariableLocal(?string $variableName, ?bool $deserializeObjectValue = true)
    {
        return $this->getValueFromVariableInstance($deserializeObjectValue, $this->getVariableInstanceLocal($variableName));
    }

    protected function getValueFromVariableInstance(bool $deserializeObjectValue, ?CoreVariableInstanceInterface $variableInstance = null)
    {
        if ($variableInstance !== null) {
            $typedValue = $variableInstance->getTypedValue($deserializeObjectValue);
            if ($typedValue !== null) {
                return $typedValue->getValue();
            }
        }
        return null;
    }

    public function getVariableTyped(?string $variableName, ?bool $deserializeValue = null): ?TypedValueInterface
    {
        $deserializeValue = $deserializeValue ?? true;
        return $this->getTypedValueFromVariableInstance($deserializeValue, $this->getVariableInstance($variableName));
    }

    public function getVariableLocalTyped(?string $variableName, ?bool $deserializeValue = null): ?TypedValueInterface
    {
        $deserializeValue = $deserializeValue ?? true;
        return $this->getTypedValueFromVariableInstance($deserializeValue, $this->getVariableInstanceLocal($variableName));
    }

    private function getTypedValueFromVariableInstance(bool $deserializeValue, CoreVariableInstanceInterface $variableInstance): ?TypedValueInterface
    {
        if ($variableInstance !== null) {
            return $variableInstance->getTypedValue($deserializeValue);
        } else {
            return null;
        }
    }

    public function getVariableInstance(?string $variableName): ?CoreVariableInstanceInterface
    {
        $variableInstance = $this->getVariableInstanceLocal($variableName);
        if ($variableInstance !== null) {
            return $variableInstance;
        }
        $parentScope = $this->getParentVariableScope();
        if ($parentScope !== null) {
            return $parentScope->getVariableInstance($variableName);
        }
        return null;
    }

    public function getVariableInstanceLocal(?string $name): ?CoreVariableInstanceInterface
    {
        return $this->getVariableStore()->getVariable($name);
    }

    public function getVariableInstancesLocal(?array $variableNames = []): array
    {
        return $this->getVariableStore()->getVariables($variableNames);
    }

    public function hasVariables(): bool
    {
        if (!$this->getVariableStore()->isEmpty()) {
            return true;
        }
        $parentScope = $this->getParentVariableScope();
        return $parentScope !== null && $parentScope->hasVariables();
    }

    public function hasVariablesLocal(): bool
    {
        return !$this->getVariableStore()->isEmpty();
    }

    public function hasVariable(?string $variableName): bool
    {
        if ($this->hasVariableLocal($variableName)) {
            return true;
        }
        $parentScope = $this->getParentVariableScope();
        return $parentScope !== null && $parentScope->hasVariable($variableName);
    }

    public function hasVariableLocal(?string $variableName): bool
    {
        return $this->getVariableStore()->containsKey($variableName);
    }

    protected function collectVariableNames(array $variableNames): array
    {
        $parentScope = $this->getParentVariableScope();
        if ($parentScope !== null) {
            $variableNames = array_merge($variableNames, $parentScope->collectVariableNames($variableNames));
        }
        foreach ($this->getVariableStore()->getVariables() as $variableInstance) {
            $variableNames[] = $variableInstance->getName();
        }
        return $variableNames;
    }

    public function getVariableNames(): array
    {
        return $this->collectVariableNames([]);
    }

    public function getVariableNamesLocal(): array
    {
        return $this->getVariableStore()->getKeys();
    }

    public function setVariables($variables, ?bool $skipSerializationFormatCheck = null): void
    {
        $scope = $this;
        VariableUtil::setVariables($variables, new class ($scope, $skipSerializationFormatCheck) implements SetVariableFunctionInterface {
            private $scope;
            private $skipSerializationFormatCheck;

            public function __construct(AbstractVariableScope $scope, ?bool $skipSerializationFormatCheck = null)
            {
                $this->scope = $scope;
                $this->skipSerializationFormatCheck = $skipSerializationFormatCheck;
            }

            public function apply(?string $variableName, $variableValue): void
            {
                $this->scope->setVariable($variableName, $variableValue, $this->scope->getSourceActivityVariableScope(), $this->skipSerializationFormatCheck);
            }
        });
    }

    public function setVariablesLocal($variables, ?bool $skipSerializationFormatCheck = null): void
    {
        $scope = $this;
        VariableUtil::setVariables($variables, new class ($scope, $skipSerializationFormatCheck) implements SetVariableFunctionInterface {
            private $scope;
            private $skipSerializationFormatCheck;

            public function __construct(AbstractVariableScope $scope, ?bool $skipSerializationFormatCheck = null)
            {
                $this->scope = $scope;
                $this->skipSerializationFormatCheck = $skipSerializationFormatCheck;
            }

            public function apply(?string $variableName, $variableValue): void
            {
                $this->scope->setVariableLocal($variableName, $variableValue, $this->scope->getSourceActivityVariableScope(), $this->skipSerializationFormatCheck);
            }
        });
    }

    public function removeVariables(?array $variableNames = []): void
    {
        if (!empty($variableNames)) {
            foreach ($variableNames as $variableName) {
                $this->removeVariable($variableName);
            }
        } else {
            foreach ($this->getVariableStore()->getVariables() as $variableInstance) {
                $this->invokeVariableLifecycleListenersDelete($variableInstance, $this->getSourceActivityVariableScope());
            }

            $this->getVariableStore()->removeVariables();
        }
    }

    public function removeVariablesLocal(?array $variableNames = []): void
    {
        if (empty($variableNames)) {
            $variableNames = $this->getVariableNamesLocal();
            foreach ($variableNames as $variableName) {
                $this->removeVariableLocal($variableName);
            }
        } else {
            foreach ($variableNames as $variableName) {
                $this->removeVariableLocal($variableName);
            }
        }
    }

    public function setVariable(?string $variableName, $value, /*AbstractVariableScope|bool|string*/...$args): void
    {
        if ($value instanceof TypedValueInterface) {
            if (!empty($args)) {
                if ($args[0] instanceof AbstractVariableScope) {
                    $sourceActivityVariableScope = $args[0];
                }
                if (count($args) == 2) {
                    $skipPhpSerializationFormatCheck = $args[1] ?? false;
                }
            }
            $skipPhpSerializationFormatCheck ??= false;
            if ($this->hasVariableLocal($variableName)) {
                $this->setVariableLocal($variableName, $value, $sourceActivityVariableScope, $skipPhpSerializationFormatCheck);
                return;
            }
            $parentVariableScope = $this->getParentVariableScope();
            if ($parentVariableScope !== null) {
                if ($sourceActivityVariableScope === null) {
                    $parentVariableScope->setVariable($variableName, $value, $skipPhpSerializationFormatCheck);
                } else {
                    $parentVariableScope->setVariable($variableName, $value, $sourceActivityVariableScope, $skipPhpSerializationFormatCheck);
                }
                return;
            }

            $this->setVariableLocal($variableName, $value, $sourceActivityVariableScope);
        } else {
            $typedValue = Variables::untypedValue($value);
            $sourceActivityVariableScope = $this->getSourceActivityVariableScope();
            if (count($args) == 1 && is_bool($args[0])) {
                $skipPhpSerializationFormatCheck = $args[0];
            } else {
                $skipPhpSerializationFormatCheck = false;
            }
            $this->setVariable($variableName, $typedValue, $sourceActivityVariableScope, $skipPhpSerializationFormatCheck);
        }
    }

    public function setVariableLocal(?string $variableName, $value, /*AbstractVariableScope|string|bool*/...$args): void
    {
        if ($value instanceof TypedValueInterface) {
            if (!empty($args)) {
                if ($args[0] instanceof AbstractVariableScope) {
                    $sourceActivityExecution = $args[0];
                }
                if (count($args) == 2) {
                    $skipPhpSerializationFormatCheck = $args[1] ?? false;
                }
            }
            $sourceActivityExecution ??= $this->getSourceActivityVariableScope();
            $skipPhpSerializationFormatCheck ??= false;

            if (!$skipPhpSerializationFormatCheck) {
                VariableUtil::checkPhpSerialization($variableName, $value);
            }

            $variableStore = $this->getVariableStore();
            if ($variableStore->containsKey($variableName)) {
                $existingInstance = $variableStore->getVariable($variableName);
                $previousValue = $existingInstance->getTypedValue(false);
                if ($value->isTransient() != $previousValue->isTransient()) {
                    //throw ProcessEngineLogger.CORE_LOGGER.transientVariableException($variableName);
                }
                $existingInstance->setValue($value);
                $this->invokeVariableLifecycleListenersUpdate($existingInstance, $sourceActivityExecution);
            } elseif (!$value->isTransient() && $variableStore->isRemoved($variableName)) {
                $existingInstance = $variableStore->getRemovedVariable($variableName);

                $existingInstance->setValue($value);
                $this->getVariableStore()->addVariable($existingInstance);
                $this->invokeVariableLifecycleListenersUpdate($existingInstance, $sourceActivityExecution);

                $dbEntityManager = Context::getCommandContext()->getDbEntityManager();
                $dbEntityManager->undoDelete($existingInstance);
            } else {
                $variableValue = $this->getVariableInstanceFactory()->build($variableName, $value, $value->isTransient());
                $this->getVariableStore()->addVariable($variableValue);
                $this->invokeVariableLifecycleListenersCreate($variableValue, $sourceActivityExecution);
            }
        } else {
            $typedValue = Variables::untypedValue($value);
            $sourceActivityVariableScope = $this->getSourceActivityVariableScope();
            if (count($args) == 1 && is_bool($args[0])) {
                $skipPhpSerializationFormatCheck = $args[0];
            } else {
                $skipPhpSerializationFormatCheck = false;
            }
            $this->setVariableLocal($variableName, $typedValue, $sourceActivityVariableScope, $skipPhpSerializationFormatCheck);
        }
    }

    protected function invokeVariableLifecycleListenersCreate(
        CoreVariableInstanceInterface $variableInstance,
        AbstractVariableScope $sourceScope,
        ?array $lifecycleListeners = []
    ): void {
        if (empty($lifecycleListeners)) {
            $lifecycleListeners = $this->getVariableInstanceLifecycleListeners();
        }
        foreach ($lifecycleListeners as $lifecycleListener) {
            $lifecycleListener->onCreate($variableInstance, $sourceScope);
        }
    }

    protected function invokeVariableLifecycleListenersDelete(
        CoreVariableInstanceInterface $variableInstance,
        AbstractVariableScope $sourceScope,
        ?array $lifecycleListeners = []
    ): void {
        if (empty($lifecycleListeners)) {
            $lifecycleListeners = $this->getVariableInstanceLifecycleListeners();
        }
        foreach ($lifecycleListeners as $lifecycleListener) {
            $lifecycleListener->onDelete($variableInstance, $sourceScope);
        }
    }

    protected function invokeVariableLifecycleListenersUpdate(
        CoreVariableInstanceInterface $variableInstance,
        ?AbstractVariableScope $sourceScope,
        ?array $lifecycleListeners = []
    ): void {
        if (empty($lifecycleListeners)) {
            $lifecycleListeners = $this->getVariableInstanceLifecycleListeners();
        }
        foreach ($lifecycleListeners as $lifecycleListener) {
            $lifecycleListener->onUpdate($variableInstance, $sourceScope);
        }
    }

    public function removeVariable(?string $variableName, ?AbstractVariableScope $sourceActivityExecution = null): void
    {
        if ($sourceActivityExecution === null) {
            $sourceActivityExecution = $this->getSourceActivityVariableScope();
        }
        if ($this->getVariableStore()->containsKey($variableName)) {
            $this->removeVariableLocal($variableName, $sourceActivityExecution);
            return;
        }
        $parentVariableScope = $this->getParentVariableScope();
        if ($parentVariableScope !== null) {
            if ($sourceActivityExecution === null) {
                $parentVariableScope->removeVariable($variableName);
            } else {
                $parentVariableScope->removeVariable($variableName, $sourceActivityExecution);
            }
        }
    }

    public function getSourceActivityVariableScope(): AbstractVariableScope
    {
        return $this;
    }

    public function removeVariableLocal(?string $variableName, ?AbstractVariableScope $sourceActivityExecution = null): void
    {
        if ($sourceActivityExecution === null) {
            $sourceActivityExecution = $this->getSourceActivityVariableScope();
        }
        if ($this->getVariableStore()->containsKey($variableName)) {
            $variableInstance = $this->getVariableStore()->getVariable($variableName);

            $this->invokeVariableLifecycleListenersDelete($variableInstance, $sourceActivityExecution);
            $this->getVariableStore()->removeVariable($variableName);
        }
    }

    public function getCachedElContext(): ELContext
    {
        return $this->cachedElContext;
    }
    public function setCachedElContext(ELContext $cachedElContext): void
    {
        $this->cachedElContext = $cachedElContext;
    }

    public function dispatchEvent(VariableEvent $variableEvent): void
    {
       // default implementation does nothing
    }
}
