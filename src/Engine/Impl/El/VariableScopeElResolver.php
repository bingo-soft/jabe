<?php

namespace Jabe\Engine\Impl\El;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Bpmn\Behavior\ExternalTaskActivityBehavior;
use Jabe\Engine\Impl\Context\Context;
use El\{
    ELContext,
    ELResolver
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExternalTaskEntity,
    TaskEntity
};

class VariableScopeElResolver extends ELResolver
{
    public static $EXECUTION_KEY = "execution";
    public static $CASE_EXECUTION_KEY = "caseExecution";
    public static $TASK_KEY = "task";
    public static $EXTERNAL_TASK_KEY = "externalTask";
    public static $LOGGED_IN_USER_KEY = "authenticatedUserId";

    public function getCommonPropertyType(?ELContext $context, $base): ?string
    {
        return "object";
    }

    public function getFeatureDescriptors(?ELContext $context, $base): ?array
    {
        return null;
    }

    public function getType(?ELContext $context, $base, $property)
    {
        return "object";
    }

    public function getValue(?ELContext $context, $base, $property)
    {
        $object = $context->getContext(VariableScopeInterface::class);
        if ($object !== null) {
            $variableScope = $object;
            if ($base === null) {
                $variable = strval($property);

                if (
                    self::$EXECUTION_KEY == $property && $variableScope instanceof ExecutionEntity
                    || self::$TASK_KEY == $property && $variableScope instanceof TaskEntity
                    /*|| variableScope instanceof CaseExecutionEntity
                     && (CASE_EXECUTION_KEY.equals(property) || EXECUTION_KEY.equals(property)) */
                ) {
                    $context->setPropertyResolved(true);
                    return $variableScope;
                } elseif (
                    self::$EXTERNAL_TASK_KEY == $property
                    && $variableScope instanceof ExecutionEntity
                    && $variableScope->getActivity() !== null
                    && $variableScope->getActivity()->getActivityBehavior() instanceof ExternalTaskActivityBehavior
                ) {
                    $externalTasks = $variableScope->getExternalTasks();
                    if (count($externalTasks) != 1) {
                        throw new ProcessEngineException("Could not resolve expression to single external task entity.");
                    }
                    $context->setPropertyResolved(true);
                    return $externalTasks[0];
                } elseif (self::$EXECUTION_KEY == $property && $variableScope instanceof TaskEntity) {
                    $context->setPropertyResolved(true);
                    return $variableScope->getExecution();
                } elseif (self::$LOGGED_IN_USER_KEY == $property) {
                    $context->setPropertyResolved(true);
                    return Context::getCommandContext()->getAuthenticatedUserId();
                } elseif ($variableScope->hasVariable($variable)) {
                    $context->setPropertyResolved(true); // if not set, the next elResolver in the CompositeElResolver will be called
                    return $variableScope->getVariable($variable);
                }
            }
        }

        // property resolution (eg. bean.value) will be done by the BeanElResolver (part of the CompositeElResolver)
        // It will use the bean resolved in this resolver as base.

        return null;
    }

    public function isReadOnly(?ELContext $context, $base, $property): bool
    {
        if ($base === null) {
            $variable = strval($property);
            $object = $context->getContext(VariableScopeInterface::class);
            return $object !== null && !$object->hasVariable($variable);
        }
        return true;
    }

    public function setValue(?ELContext $context, $base, $property, $value): void
    {
        if ($base === null) {
            $variable = strval($property);
            $object = $context->getContext(VariableScopeInterface::class);
            if ($object !== null) {
                $variableScope = $object;
                if ($variableScope->hasVariable($variable)) {
                    $variableScope->setVariable($variable, $value);
                    $context->setPropertyResolved(true);
                }
            }
        }
    }
}
