<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Jabe\Impl\Util\EnsureUtil;
use Jabe\ProcessEngineException;
use Jabe\Delegate\ExpressionInterface;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Bpmn\Helper\CompensationUtil;
use Jabe\Impl\Pvm\PvmActivityInterface;
use Jabe\Impl\Pvm\Delegate\{
    ActivityExecutionInterface,
    CompositeActivityBehaviorInterface,
    ModificationObserverBehaviorInterface
};
use Jabe\Impl\Pvm\Process\ActivityImpl;

abstract class MultiInstanceActivityBehavior extends AbstractBpmnActivityBehavior implements CompositeActivityBehaviorInterface, ModificationObserverBehaviorInterface
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    // Variable names for mi-body scoped variables (as described in spec)
    public const NUMBER_OF_INSTANCES = "nrOfInstances";
    public const NUMBER_OF_ACTIVE_INSTANCES = "nrOfActiveInstances";
    public const NUMBER_OF_COMPLETED_INSTANCES = "nrOfCompletedInstances";

    // Variable names for mi-instance scoped variables (as described in the spec)
    public const LOOP_COUNTER = "loopCounter";

    protected $loopCardinalityExpression;
    protected $completionConditionExpression;
    protected $collectionExpression;
    protected $collectionVariable;
    protected $collectionElementVariable;

    public function __construct()
    {
        parent::__construct();
    }

    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        $nrOfInstances = $this->resolveNrOfInstances($execution);
        if ($nrOfInstances == 0) {
            $this->leave($execution);
        } elseif ($nrOfInstances < 0) {
            //throw LOG.invalidAmountException("instances", nrOfInstances);
        } else {
            $this->createInstances($execution, $nrOfInstances);
        }
    }

    protected function performInstance(ActivityExecutionInterface $execution, PvmActivityInterface $activity, int $loopCounter): void
    {
        $this->setLoopVariable($execution, self::LOOP_COUNTER, $loopCounter);
        $this->evaluateCollectionVariable($execution, $loopCounter);
        $execution->setEnded(false);
        $execution->setActive(true);
        $execution->executeActivity($activity);
    }

    protected function evaluateCollectionVariable(ActivityExecutionInterface $execution, int $loopCounter): void
    {
        if ($this->usesCollection() && $this->collectionElementVariable !== null) {
            $collection = null;
            if ($this->collectionExpression !== null) {
                $collection = $this->collectionExpression->getValue($execution);
            } elseif ($this->collectionVariable !== null) {
                $collection = $execution->getVariable($this->collectionVariable);
            }

            $value = $this->getElementAtIndex($loopCounter, $collection);
            $this->setLoopVariable($execution, $this->collectionElementVariable, $value);
        }
    }

    abstract protected function createInstances(ActivityExecutionInterface $execution, int $nrOfInstances): void;

    // Helpers //////////////////////////////////////////////////////////////////////

    protected function resolveNrOfInstances(ActivityExecutionInterface $execution): int
    {
        $nrOfInstances = -1;
        if ($this->loopCardinalityExpression !== null) {
            $nrOfInstances = $this->resolveLoopCardinality($execution);
        } elseif ($this->collectionExpression !== null) {
            $obj = $this->collectionExpression->getValue($execution);
            if (!is_array($obj)) {
                //throw LOG.unresolvableExpressionException($collectionExpression->getExpressionText(), "Collection");
            }
            $nrOfInstances = count($obj);
        } elseif ($this->collectionVariable !== null) {
            $obj = $execution->getVariable($this->collectionVariable);
            if (!is_array($obj)) {
                //throw LOG.invalidVariableTypeException(collectionVariable, "Collection");
            }
            $nrOfInstances = count($obj);
        } else {
            //throw LOG.resolveCollectionExpressionOrVariableReferenceException();
        }
        return $nrOfInstances;
    }

    protected function getElementAtIndex(int $i, array $collection)
    {
        if (array_key_exists($i, $collection)) {
            return $collection[$i];
        }
        return null;
    }

    protected function usesCollection(): bool
    {
        return $this->collectionExpression !== null
                    || $this->collectionVariable !== null;
    }

    protected function resolveLoopCardinality(ActivityExecutionInterface $execution): int
    {
        // Using Number since expr can evaluate to eg. Long (which is also the default for Juel)
        $value = $this->loopCardinalityExpression->getValue($execution);
        if (is_numeric($value)) {
            return intval($value);
        } elseif (is_string($value)) {
            return intval($value);
        } else {
            //throw LOG.expressionNotANumberException("loopCardinality", loopCardinalityExpression->getExpressionText());
        }
    }

    protected function completionConditionSatisfied(ActivityExecutionInterface $execution): bool
    {
        if ($this->completionConditionExpression !== null) {
            $value = $this->completionConditionExpression->getValue($execution);
            if (!is_bool($value)) {
                //throw LOG.expressionNotBooleanException("completionCondition", completionConditionExpression->getExpressionText());
            }
            //LOG.multiInstanceCompletionConditionState(booleanValue);
            return $value;
        }
        return false;
    }

    public function doLeave(ActivityExecutionInterface $execution): void
    {
        CompensationUtil::createEventScopeExecution($execution);

        parent::doLeave($execution);
    }

    /**
     * Get the inner activity of the multi instance $execution->
     *
     * @param execution
     *          of multi instance activity
     * @return inner activity
     */
    public function getInnerActivity(PvmActivityInterface $miBodyActivity): ActivityImpl
    {
        foreach ($miBodyActivity->getActivities() as $activity) {
            $innerActivity = $activity;
            // note that miBody can contains also a compensation handler
            if (!$innerActivity->isCompensationHandler()) {
                return $innerActivity;
            }
        }
        throw new ProcessEngineException("inner activity of multi instance body activity '" . $miBodyActivity->getId() . "' not found");
    }

    protected function setLoopVariable(ActivityExecutionInterface $execution, ?string $variableName, $value): void
    {
        $execution->setVariableLocal($variableName, $value);
    }

    protected function getLoopVariable(ActivityExecutionInterface $execution, ?string $variableName): int
    {
        $value = $execution->getVariableLocalTyped($variableName);
        EnsureUtil::ensureNotNull("The variable \"" . $variableName . "\" could not be found in execution with id " . $execution->getId(), "value", $value);
        return $value->getValue();
    }

    protected function getLocalLoopVariable(ActivityExecutionInterface $execution, ?string $variableName): int
    {
        return $execution->getVariableLocal($variableName);
    }

    public function hasLoopVariable(ActivityExecutionInterface $execution, ?string $variableName): bool
    {
        return $execution->hasVariableLocal($variableName);
    }

    public function removeLoopVariable(ActivityExecutionInterface $execution, ?string $variableName): void
    {
        $execution->removeVariableLocal($variableName);
    }

    // Getters and Setters ///////////////////////////////////////////////////////////

    public function getLoopCardinalityExpression(): ?ExpressionInterface
    {
        return $this->loopCardinalityExpression;
    }

    public function setLoopCardinalityExpression(ExpressionInterface $loopCardinalityExpression): void
    {
        $this->loopCardinalityExpression = $loopCardinalityExpression;
    }

    public function getCompletionConditionExpression(): ?ExpressionInterface
    {
        return $this->completionConditionExpression;
    }

    public function setCompletionConditionExpression(ExpressionInterface $completionConditionExpression): void
    {
        $this->completionConditionExpression = $completionConditionExpression;
    }

    public function getCollectionExpression(): ?ExpressionInterface
    {
        return $this->collectionExpression;
    }

    public function setCollectionExpression(ExpressionInterface $collectionExpression): void
    {
        $this->collectionExpression = $collectionExpression;
    }

    public function getCollectionVariable(): ?string
    {
        return $this->collectionVariable;
    }

    public function setCollectionVariable(?string $collectionVariable): void
    {
        $this->collectionVariable = $collectionVariable;
    }

    public function getCollectionElementVariable(): ?string
    {
        return $this->collectionElementVariable;
    }

    public function setCollectionElementVariable(?string $collectionElementVariable): void
    {
        $this->collectionElementVariable = $collectionElementVariable;
    }
}
