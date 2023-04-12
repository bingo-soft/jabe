<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\Impl\Context\{
    Context,
    ProcessApplicationContextUtil
};
use Jabe\Impl\Core\Variable\Mapping\Value\ParameterValueProviderInterface;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Pvm\Process\ProcessDefinitionImpl;

abstract class DefaultPriorityProvider implements PriorityProviderInterface
{
    /**
     * The default priority.
     */
    public const DEFAULT_PRIORITY = 0;

    /**
     * The default priority in case of resolution failure.
     */
    public const DEFAULT_PRIORITY_ON_RESOLUTION_FAILURE = 0;

    /**
     * Returns the default priority.
     *
     * @return int the default priority
     */
    public function getDefaultPriority(): int
    {
        return self::DEFAULT_PRIORITY;
    }

    /**
     * Returns the default priority in case of resolution failure.
     *
     * @return int the default priority
     */
    public function getDefaultPriorityOnResolutionFailure(): int
    {
        return self::DEFAULT_PRIORITY_ON_RESOLUTION_FAILURE;
    }

    /**
     * Evaluates a given value provider with the given execution entity to determine
     * the correct value. The error message heading is used for the error message
     * if the validation fails because the value is no valid priority.
     *
     * @param valueProvider the provider which contains the value
     * @param execution the execution entity
     * @param errorMessageHeading the heading which is used for the error message
     * @return int the valid priority value
     */
    protected function evaluateValueProvider(ParameterValueProviderInterface $valueProvider, ?ExecutionEntity $execution, ?string $errorMessageHeading): int
    {
        $value = null;
        try {
            $value = $valueProvider->getValue($execution);
        } catch (ProcessEngineException $e) {
            if (
                Context::getProcessEngineConfiguration()->isEnableGracefulDegradationOnContextSwitchFailure()
                && $this->isSymptomOfContextSwitchFailure($e, $execution)
            ) {
                $value = $this->getDefaultPriorityOnResolutionFailure();
                $this->logNotDeterminingPriority($execution, $value, $e);
            } else {
                throw $e;
            }
        }

        if (!is_int($value)) {
            throw new ProcessEngineException($errorMessageHeading . ": Priority value is not an Integer");
        } else {
            $numberValue = $value;
            if ($this->isValidLongValue($numberValue)) {
                return $numberValue;
            } else {
                throw new ProcessEngineException($errorMessageHeading . ": Priority value must be either Short, Integer, or Long");
            }
        }
    }

    public function determinePriority(?ExecutionEntity $execution, $param, ?string $jobDefinitionId): int
    {
        if ($param !== null || $execution !== null) {
            $specificPriority = $this->getSpecificPriority($execution, $param, $jobDefinitionId);
            if ($specificPriority !== null) {
                return $specificPriority;
            }

            $processDefinitionPriority = $this->getProcessDefinitionPriority($execution, $param);
            if ($processDefinitionPriority !== null) {
                return $processDefinitionPriority;
            }
        }
        return $this->getDefaultPriority();
    }

    /**
     * Returns the priority defined in the specific entity. Like a job definition priority or
     * an activity priority. The result can also be null in that case the process
     * priority will be used.
     *
     * @param execution the current execution
     * @param param the generic param
     * @param jobDefinitionId the job definition id if related to a job
     * @return int the specific priority
     */
    abstract protected function getSpecificPriority(ExecutionEntity $execution, $param, ?string $jobDefinitionId): ?int;

    /**
     * Returns the priority defined in the process definition. Can also be null
     * in that case the fallback is the default priority.
     *
     * @param execution the current execution
     * @param param the generic param
     * @return int the priority defined in the process definition
     */
    abstract protected function getProcessDefinitionPriority(ExecutionEntity $execution, $param): ?int;

    /**
     * Returns the priority which is defined in the given process definition.
     * The priority value is identified with the given propertyKey.
     * Returns null if the process definition is null or no priority was defined.
     *
     * @param processDefinition the process definition that should contains the priority
     * @param propertyKey the key which identifies the property
     * @param execution the current execution
     * @param errorMsgHead the error message header which is used if the evaluation fails
     * @return int the priority defined in the given process
     */
    protected function getProcessDefinedPriority(ProcessDefinitionImpl $processDefinition, ?string $propertyKey, ?ExecutionEntity $execution, ?string $errorMsgHead): ?int
    {
        if ($processDefinition !== null) {
            $priorityProvider = $processDefinition->getProperty($propertyKey);
            if ($priorityProvider !== null) {
                return $this->evaluateValueProvider($priorityProvider, $execution, $errorMsgHead);
            }
        }
        return null;
    }
    /**
     * Logs the exception which was thrown if the priority can not be determined.
     *
     * @param execution the current execution entity
     * @param value the current value
     * @param e the exception which was catched
     */
    abstract protected function logNotDeterminingPriority(ExecutionEntity $execution, $value, ProcessEngineException $e): void;

    protected function isSymptomOfContextSwitchFailure(\Throwable $t, ExecutionEntity $contextExecution): bool
    {
        // a context switch failure can occur, if the current engine has no PA registration for the deployment
        // subclasses may assert the actual throwable to narrow down the diagnose
        return ProcessApplicationContextUtil::getTargetProcessApplication($contextExecution) === null;
    }

    /**
     * Checks if the given number is a valid long value.
     * @param value the number which should be checked
     * @return bool true if is a valid long value, false otherwise
     */
    protected function isValidLongValue($value): bool
    {
        return is_int($value);
    }
}
