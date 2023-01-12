<?php

namespace Jabe\Impl\Cmd;

use Jabe\ProcessEngineException;
use Jabe\Exception\NotValidException;
use Jabe\Impl\ActivityExecutionTreeMapping;
use Jabe\Impl\Bpmn\Behavior\SequentialMultiInstanceActivityBehavior;
use Jabe\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Impl\Core\Model\CoreModelElement;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Pvm\{
    PvmActivityInterface,
    PvmScopeInterface,
    PvmTransitionInterface
};
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ActivityStartBehavior,
    ProcessDefinitionImpl,
    ScopeImpl,
    TransitionImpl
};
use Jabe\Impl\Tree\{
    ActivityStackCollector,
    FlowScopeWalker,
    ReferenceWalker,
    WalkConditionInterface
};
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Variable\VariableMapInterface;
use Jabe\Variable\Impl\VariableMapImpl;

abstract class AbstractInstantiationCmd extends AbstractProcessInstanceModificationCommand
{
    protected $variables;
    protected $variablesLocal;
    protected $ancestorActivityInstanceId;

    public function __construct(?string $processInstanceId, ?string $ancestorActivityInstanceId = null)
    {
        parent::__construct($processInstanceId);
        $this->ancestorActivityInstanceId = $ancestorActivityInstanceId;
        $this->variables = new VariableMapImpl();
        $this->variablesLocal = new VariableMapImpl();
    }

    public function addVariable(?string $name, $value): void
    {
        $this->variables->put($name, $value);
    }

    public function addVariableLocal(?string $name, $value): void
    {
        $this->variablesLocal->put($name, $value);
    }

    public function addVariables(array $variables): void
    {
        $this->variables->putAll($variables);
    }

    public function addVariablesLocal(array $variables): void
    {
        $this->variablesLocal->putAll($variables);
    }

    public function getVariables(): VariableMapInterface
    {
        return $this->variables;
    }

    public function getVariablesLocal(): VariableMapInterface
    {
        return $this->variablesLocal;
    }

    public function execute(CommandContext $commandContext)
    {
        $processInstance = $commandContext->getExecutionManager()->findExecutionById($this->processInstanceId);

        $processDefinition = $processInstance->getProcessDefinition();

        $elementToInstantiate = $this->getTargetElement($processDefinition);

        EnsureUtil::ensureNotNull(
            $this->describeFailure("Element '" . $this->getTargetElementId() . "' does not exist in process '" . $processDefinition->getId() . "'"),
            "element",
            $elementToInstantiate
        );

        // rebuild the mapping because the execution tree changes with every iteration
        $mapping = new ActivityExecutionTreeMapping($commandContext, $this->processInstanceId);

        // before instantiating an activity, two things have to be determined:
        //
        // activityStack:
        // For the activity to instantiate, we build a stack of parent flow scopes
        // for which no executions exist yet and that have to be instantiated
        //
        // scopeExecution:
        // This is typically the execution under which a new sub tree has to be created.
        // if an explicit ancestor activity instance is set:
        //   - this is the scope execution for that ancestor activity instance
        //   - throws exception if that scope execution is not in the parent hierarchy
        //     of the activity to be started
        // if no explicit ancestor activity instance is set:
        //   - this is the execution of the first parent/ancestor flow scope that has an execution
        //   - throws an exception if there is more than one such execution

        $targetFlowScope = $this->getTargetFlowScope($processDefinition);

        // prepare to walk up the flow scope hierarchy and collect the flow scope activities
        $stackCollector = new ActivityStackCollector();
        $walker = new FlowScopeWalker($targetFlowScope);
        $walker->addPreVisitor($stackCollector);

        $scopeExecution = null;

        // if no explicit ancestor activity instance is set
        if ($this->ancestorActivityInstanceId === null) {
            // walk until a scope is reached for which executions exist
            $walker->walkWhile(new class ($processDefinition, $mapping) implements WalkConditionInterface {

                private $processDefinition;
                private $mapping;

                public function __construct(ProcessDefinitionImpl $processDefinition, ActivityExecutionTreeMapping $mapping)
                {
                    $this->processDefinition = $processDefinition;
                    $this->mapping = $mapping;
                }

                public function isFulfilled(ScopeImpl $element): bool
                {
                    return !empty($this->mapping->getExecutions($element)) || $element == $this->processDefinition;
                }
            });

            $flowScopeExecutions = $mapping->getExecutions($walker->getCurrentElement());

            if (count($flowScopeExecutions) > 1) {
                throw new ProcessEngineException("Ancestor activity execution is ambiguous for activity " . $targetFlowScope);
            }
            $scopeExecution = $flowScopeExecutions[0];
        } else {
            $processInstanceId = $this->processInstanceId;
            $tree = $commandContext->runWithoutAuthorization(function () use ($commandContext, $processInstanceId) {
                $cmd = new GetActivityInstanceCmd($processInstanceId);
                return $cmd->execute($commandContext);
            });

            $ancestorInstance = $this->findActivityInstance($tree, $this->ancestorActivityInstanceId);
            EnsureUtil::ensureNotNull(
                describeFailure("Ancestor activity instance '" . $this->ancestorActivityInstanceId . "' does not exist"),
                "ancestorInstance",
                $ancestorInstance
            );

            // determine ancestor activity scope execution and activity
            $ancestorScopeExecution = $this->getScopeExecutionForActivityInstance(
                $processInstance,
                $mapping,
                $ancestorInstance
            );
            $ancestorScope = $this->getScopeForActivityInstance($processDefinition, $ancestorInstance);

            // walk until the scope of the ancestor scope execution is reached
            $walker->walkWhile(new class ($mapping, $ancestorScope, $ancestorScopeExecution, $processDefinition) implements WalkConditionInterface {
                private $mapping;
                private $ancestorScope;
                private $ancestorScopeExecution;
                private $processDefinition;

                public function __construct($mapping, $ancestorScope, $ancestorScopeExecution, $processDefinition)
                {
                    $this->mapping = $mapping;
                    $this->ancestorScope = $ancestorScope;
                    $this->ancestorScopeExecution = $ancestorScopeExecution;
                    $this->processDefinition = $processDefinition;
                }

                public function isFulfilled(ScopeImpl $element): bool
                {
                    return (
                        in_array($this->ancestorScopeExecution, $this->mapping->getExecutions($element))
                        && $element == $this->ancestorScope)
                        || $element == $this->processDefinition;
                }
            });

            $flowScopeExecutions = $mapping->getExecutions($walker->getCurrentElement());

            if (!in_array($ancestorScopeExecution, $flowScopeExecutions)) {
                throw new NotValidException(describeFailure("Scope execution for '" . $this->ancestorActivityInstanceId .
                "' cannot be found in parent hierarchy of flow element '" . $elementToInstantiate->getId() . "'"));
            }

            $scopeExecution = $ancestorScopeExecution;
        }

        $activitiesToInstantiate = $stackCollector->getActivityStack();
        $activitiesToInstantiate = array_reverse($activitiesToInstantiate);

        // We have to make a distinction between
        // - "regular" activities for which the activity stack can be instantiated and started
        //   right away
        // - interrupting or cancelling activities for which we have to ensure that
        //   the interruption and cancellation takes place before we instantiate the activity stack
        $topMostActivity = null;
        $flowScope = null;
        if (!empty($activitiesToInstantiate)) {
            $topMostActivity = $activitiesToInstantiate[0];
            $flowScope = $topMostActivity->getFlowScope();
        } elseif (is_a($elementToInstantiate, ActivityImpl::class)) {
            $topMostActivity = $elementToInstantiate;
            $flowScope = $topMostActivity->getFlowScope();
        } elseif (is_a($elementToInstantiate, TransitionImpl::class)) {
            $transitionToInstantiate = $elementToInstantiate;
            $flowScope = $transitionToInstantiate->getSource()->getFlowScope();
        }

        if (!$this->supportsConcurrentChildInstantiation($flowScope)) {
            throw new ProcessEngineException(
                "Concurrent instantiation not possible for activities in scope " . $flowScope->getId()
            );
        }

        $startBehavior = ActivityStartBehavior::CONCURRENT_IN_FLOW_SCOPE;
        if ($topMostActivity !== null) {
            $startBehavior = $topMostActivity->getActivityStartBehavior();

            if (!empty($activitiesToInstantiate)) {
                // this is in BPMN relevant if there is an interrupting event sub process.
                // we have to distinguish between instantiation of the start event and any other activity.
                // instantiation of the start event means interrupting behavior; instantiation
                // of any other task means no interruption.
                $initialActivity = null;
                $props = $topMostActivity->getProperties();
                if (array_key_exists(BpmnProperties::INITIAL_ACTIVITY, $props)) {
                    $initialActivity = $props[BpmnProperties::INITIAL_ACTIVITY];
                }
                $secondTopMostActivity = null;
                if (count($activitiesToInstantiate) > 1) {
                    $secondTopMostActivity = $activitiesToInstantiate[1];
                } elseif (is_a($elementToInstantiate, ActivityImpl::class)) {
                    $secondTopMostActivity = $elementToInstantiate;
                }

                if ($initialActivity != $secondTopMostActivity) {
                    $startBehavior = ActivityStartBehavior::CONCURRENT_IN_FLOW_SCOPE;
                }
            }
        }

        switch ($startBehavior) {
            case self::CANCEL_EVENT_SCOPE:
                $scopeToCancel = $topMostActivity->getEventScope();
                $executionToCancel = $this->getSingleExecutionForScope($mapping, $scopeToCancel);
                if ($executionToCancel !== null) {
                    $executionToCancel->deleteCascade("Cancelling activity " . $topMostActivity . " executed.", $this->skipCustomListeners, $this->skipIoMappings);
                    $this->instantiate($executionToCancel->getParent(), $activitiesToInstantiate, $elementToInstantiate);
                } else {
                    $flowScopeExecution = $this->getSingleExecutionForScope($mapping, $topMostActivity->getFlowScope());
                    $this->instantiateConcurrent($flowScopeExecution, $activitiesToInstantiate, $elementToInstantiate);
                }
                break;
            case self::INTERRUPT_EVENT_SCOPE:
                 $scopeToCancel = $topMostActivity->getEventScope();
                $executionToCancel = $this->getSingleExecutionForScope($mapping, $scopeToCancel);
                $executionToCancel->interrupt("Interrupting activity " . $topMostActivity . " executed.", $this->skipCustomListeners, $this->skipIoMappings, false);
                $executionToCancel->setActivity(null);
                $executionToCancel->leaveActivityInstance();
                $this->instantiate($executionToCancel, $activitiesToInstantiate, $elementToInstantiate);
                break;
            case self::INTERRUPT_FLOW_SCOPE:
                $scopeToCancel = $topMostActivity->getFlowScope();
                $executionToCancel = $this->getSingleExecutionForScope($mapping, $scopeToCancel);
                $executionToCancel->interrupt("Interrupting activity " . $topMostActivity . " executed.", $this->skipCustomListeners, $this->skipIoMappings, false);
                $executionToCancel->setActivity(null);
                $executionToCancel->leaveActivityInstance();
                $this->instantiate($executionToCancel, $activitiesToInstantiate, $elementToInstantiate);
                break;
            default:
                // if all child executions have been cancelled
                // or this execution has ended executing its scope, it can be reused
                if (
                    !$scopeExecution->hasChildren() &&
                    ($scopeExecution->getActivity() === null || $scopeExecution->isEnded())
                ) {
                    // reuse the scope execution
                    $this->instantiate($scopeExecution, $activitiesToInstantiate, $elementToInstantiate);
                } else {
                    // if the activity is not cancelling/interrupting, it can simply be instantiated as
                    // a concurrent child of the scopeExecution
                    $this->instantiateConcurrent($scopeExecution, $activitiesToInstantiate, $elementToInstantiate);
                }
                break;
        }

        return null;
    }

    /**
     * Cannot create more than inner instance in a sequential MI construct
     */
    protected function supportsConcurrentChildInstantiation(ScopeImpl $flowScope): bool
    {
        $behavior = $flowScope->getActivityBehavior();
        return $behavior === null || !($behavior instanceof SequentialMultiInstanceActivityBehavior);
    }

    protected function getSingleExecutionForScope(ActivityExecutionTreeMapping $mapping, ScopeImpl $scope): ?ExecutionEntity
    {
        $executions = $mapping->getExecutions($scope);

        if (!empty($executions)) {
            if (count($executions) > 1) {
                throw new ProcessEngineException("Executions for activity " . $scope . " ambiguous");
            }
            return $executions[0];
        } else {
            return null;
        }
    }

    protected function isConcurrentStart(?string $startBehavior): bool
    {
        return $startBehavior == ActivityStartBehavior::DEFAULT
            || $startBehavior == ActivityStartBehavior::CONCURRENT_IN_FLOW_SCOPE;
    }

    protected function instantiate(ExecutionEntity $ancestorScopeExecution, array $parentFlowScopes, CoreModelElement $targetElement): void
    {
        if (is_a($targetElement, PvmTransitionInterface::class)) {
            $ancestorScopeExecution->executeActivities(
                $parentFlowScopes,
                null,
                $targetElement,
                $this->variables,
                $this->variablesLocal,
                $this->skipCustomListeners,
                $this->skipIoMappings
            );
        } elseif (is_a($targetElement, PvmActivityInterface::class)) {
            $ancestorScopeExecution->executeActivities(
                $parentFlowScopes,
                $targetElement,
                null,
                $this->variables,
                $this->variablesLocal,
                $this->skipCustomListeners,
                $this->skipIoMappings
            );
        } else {
            throw new ProcessEngineException("Cannot instantiate element " . $targetElement);
        }
    }

    protected function instantiateConcurrent(ExecutionEntity $ancestorScopeExecution, array $parentFlowScopes, CoreModelElement $targetElement): void
    {
        if (is_a($targetElement, PvmTransitionInterface::class)) {
            $ancestorScopeExecution->executeActivitiesConcurrent(
                $parentFlowScopes,
                null,
                $targetElement,
                $this->variables,
                $this->variablesLocal,
                $this->skipCustomListeners,
                $this->skipIoMappings
            );
        } elseif (is_a($targetElement, PvmActivityInterface::class)) {
            $ancestorScopeExecution->executeActivitiesConcurrent(
                $parentFlowScopes,
                $targetElement,
                null,
                $this->variables,
                $this->variablesLocal,
                $this->skipCustomListeners,
                $this->skipIoMappings
            );
        } else {
            throw new ProcessEngineException("Cannot instantiate element " . $targetElement);
        }
    }

    abstract protected function getTargetFlowScope(ProcessDefinitionImpl $processDefinition): ScopeImpl;

    abstract protected function getTargetElement(ProcessDefinitionImpl $processDefinition): CoreModelElement;

    abstract protected function getTargetElementId(): ?string;
}
