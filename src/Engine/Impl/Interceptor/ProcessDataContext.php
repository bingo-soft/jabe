<?php

namespace Jabe\Engine\Impl\Interceptor;

use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;

class ProcessDataContext
{
    protected const NULL_VALUE = "~NULL_VALUE~";

    protected $mdcPropertyActivityId;
    protected $mdcPropertyApplicationName;
    protected $mdcPropertyBusinessKey;
    protected $mdcPropertyDefinitionId;
    protected $mdcPropertyInstanceId;
    protected $mdcPropertyTenantId;

    protected $handleMdc = false;

    protected $activityIdStack;

    /**
     * All data stacks we need to keep for MDC logging
     */
    protected $mdcDataStacks = [];
    protected $sections;

    public function __construct(ProcessEngineConfigurationImpl $configuration, ?bool $initFromCurrentMdc = false)
    {
        $this->sections = new ProcessDataSections();
        $this->mdcPropertyActivityId = $configuration->getLoggingContextActivityId();

        // always keep track of activity ids, because those are used to
        // populate the Job#getFailedActivityId field. This is independent
        // of the logging configuration
        $this->activityIdStack = new ProcessDataStack(self::isNotBlank($this->mdcPropertyActivityId) ? $this->mdcPropertyActivityId : null);
        if (self::isNotBlank($mdcPropertyActivityId)) {
            $this->mdcDataStacks[$this->mdcPropertyActivityId] = $activityIdStack;
        }
        $this->mdcPropertyApplicationName = $configuration->getLoggingContextApplicationName();
        if (self::isNotBlank($this->mdcPropertyApplicationName)) {
            $this->mdcDataStacks[$this->mdcPropertyApplicationName] = new ProcessDataStack($this->mdcPropertyApplicationName);
        }
        $this->mdcPropertyBusinessKey = $configuration->getLoggingContextBusinessKey();
        if (self::isNotBlank($this->mdcPropertyBusinessKey)) {
            $this->mdcDataStacks[$this->mdcPropertyBusinessKey] = new ProcessDataStack($this->mdcPropertyBusinessKey);
        }
        $this->mdcPropertyDefinitionId = $configuration->getLoggingContextProcessDefinitionId();
        if (self::isNotBlank($this->mdcPropertyDefinitionId)) {
            $this->mdcDataStacks[$this->mdcPropertyDefinitionId] = new ProcessDataStack($this->mdcPropertyDefinitionId);
        }
        $this->mdcPropertyInstanceId = $configuration->getLoggingContextProcessInstanceId();
        if (self::isNotBlank($this->mdcPropertyInstanceId)) {
            $this->mdcDataStacks[$this->mdcPropertyInstanceId] = new ProcessDataStack($this->mdcPropertyInstanceId);
        }
        $this->mdcPropertyTenantId = $configuration->getLoggingContextTenantId();
        if (self::isNotBlank($this->mdcPropertyTenantId)) {
            $this->mdcDataStacks[$this->mdcPropertyTenantId] = new ProcessDataStack($this->mdcPropertyTenantId);
        }
        $handleMdc = !empty($this->mdcDataStacks);

        if ($initFromCurrentMdc) {
            foreach ($this->mdcDataStacks as $stack) {
                $valuePushed = $stack->pushCurrentValueFromMdc();
                if ($valuePushed) {
                    $this->sections->addToCurrentSection($stack);
                }
            }

            $this->sections->sealCurrentSection();
        }
    }

    /**
     * Start a new section that keeps track of the pushed properties.
     *
     * If logging context properties are defined, the MDC is updated as well. This
     * also includes clearing the MDC for the first section that is pushed for the
     * logging context so that only the current properties will be present in the
     * MDC (might be less than previously present in the MDC). The previous
     * logging context needs to be reset in the MDC when this one is closed. This
     * can be achieved by using {@link #updateMdc(String)} with the previous
     * logging context.
     *
     * @param execution
     *          the execution to retrieve the context data from
     *
     * @return bool true if the section contains any updates and therefore
     *         should be popped later by {@link #popSection()}
     */
    public function pushSection(ExecutionEntity $execution): bool
    {
        if ($this->handleMdc && $this->hasNoMdcValues()) {
            $this->clearMdc();
        }

        $numSections = count($this->sections);

        $this->addToStack($this->activityIdStack, $execution->getActivityId());
        $this->addToStack($execution->getProcessDefinitionId(), $this->mdcPropertyDefinitionId);
        $this->addToStack($execution->getProcessInstanceId(), $this->mdcPropertyInstanceId);
        $this->addToStack($execution->getTenantId(), $this->mdcPropertyTenantId);

        if (self::isNotBlank($this->mdcPropertyApplicationName)) {
            $currentPa = Context::getCurrentProcessApplication();
            if ($currentPa !== null) {
                $this->addToStack($currentPa->getName(), $this->mdcPropertyApplicationName);
            }
        }

        if (self::isNotBlank($this->mdcPropertyBusinessKey)) {
            $this->addToStack($execution->getBusinessKey(), $this->mdcPropertyBusinessKey);
        }

        $this->sections->sealCurrentSection();

        $newSectionCreated = $numSections != count($this->sections);

        return $newSectionCreated;
    }

    protected function hasNoMdcValues(): bool
    {
        foreach ($this->mdcDataStacks as $stack) {
            if (!$stack->isEmpty()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Pop the latest section, remove all pushed properties of that section and -
     * if logging context properties are defined - update the MDC accordingly.
     */
    public function popSection(): void
    {
        $this->sections->popCurrentSection();
    }

    /** Remove all logging context properties from the MDC */
    public function clearMdc(): void
    {
        if ($this->handleMdc) {
            foreach ($this->mdcDataStacks as $stack) {
                $stack->clearMdcProperty();
            }
        }
    }

    /** Update the MDC with the current values of this logging context */
    public function updateMdcFromCurrentValues(): void
    {
        if ($this->handleMdc) {
            foreach ($this->mdcDataStacks as $stack) {
                $stack->updateMdcWithCurrentValue();
            }
        }
    }

    /**
     * @return string the latest value of the activity id property if exists, <code>null</code>
     *         otherwise
     */
    public function getLatestActivityId(): ?string
    {
        return $this->activityIdStack->getCurrentValue();
    }

    protected function addToStack($valueOrStack, $propertyOrValue): void
    {
        if (is_string($valueOrStack)) {
            if (!self::isNotBlank($propertyOrValue)) {
                return;
            }

            $stack = $this->mdcDataStacks[$propertyOrValue];
            $this->addToStack($stack, $valueOrStack);
        } elseif ($valueOrStack instanceof ProcessDataStack) {
            $current = $valueOrStack->getCurrentValue();
            if (self::valuesEqual($current, $propertyOrValue)) {
                return;
            }
            $valueOrStack->pushCurrentValue($propertyOrValue);
            $this->sections->addToCurrentSection($valueOrStack);
        }
    }

    protected static function isNotBlank(?string $property): bool
    {
        return $property !== null && !empty(trim($property));
    }

    protected static function valuesEqual(?string $val1, ?string $val2): bool
    {
        return $val1 == $val2;
    }

    protected static function isNull(?string $value): bool
    {
        return $value === null || self::NULL_VALUE == $value;
    }
}
