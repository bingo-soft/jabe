<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\{
    ProcessEngineLogger,
    ProcessInstanceModificationBuilderImpl,
    ProcessInstantiationBuilderImpl
};
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ExecutionVariableSnapshotObserver,
    ProcessInstanceWithVariablesImpl,
    PropertyChange
};
use Jabe\Impl\Pvm\Process\{
    ActivityImpl,
    ProcessDefinitionImpl,
    TransitionImpl
};
use Jabe\Impl\Util\EnsureUtil;

class StartProcessInstanceAtActivitiesCmd implements CommandInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $instantiationBuilder;

    public function __construct(ProcessInstantiationBuilderImpl $instantiationBuilder)
    {
        $this->instantiationBuilder = $instantiationBuilder;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $processDefinition = (new GetDeployedProcessDefinitionCmd($this->instantiationBuilder, false))->execute($commandContext);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkCreateProcessInstance($processDefinition);
        }

        $modificationBuilder = $this->instantiationBuilder->getModificationBuilder();
        EnsureUtil::ensureNotEmpty(
            "At least one instantiation instruction required (e.g. by invoking startBefore(..), startAfter(..) or startTransition(..))",
            "instructions",
            $modificationBuilder->getModificationOperations()
        );

        // instantiate the process
        $initialActivity = $this->determineFirstActivity($processDefinition, $modificationBuilder);

        $processInstance = $processDefinition->createProcessInstance(
            $this->instantiationBuilder->getBusinessKey(),
            //instantiationBuilder.getCaseInstanceId(),
            null,
            $initialActivity
        );

        if ($this->instantiationBuilder->getTenantId() !== null) {
            $processInstance->setTenantId($this->instantiationBuilder->getTenantId());
        }

        $processInstance->setSkipCustomListeners($modificationBuilder->isSkipCustomListeners());
        $variables = $modificationBuilder->getProcessVariables();

        $variablesListener = new ExecutionVariableSnapshotObserver($processInstance);

        $processInstance->startWithoutExecuting($variables);

        // prevent ending of the process instance between instructions
        $processInstance->setPreserveScope(true);

        // apply modifications
        $instructions = $modificationBuilder->getModificationOperations();

        // The "starting" flag controls if historic details are marked as initial.
        // The documented behavior of this feature is that initial variables
        // are only set if there is a single start activity. Accordingly,
        // we reset the flag in case we have more than one start instruction.
        $processInstance->setStarting(count($instructions) == 1);

        for ($i = 0; $i < count($instructions); $i += 1) {
            $instruction = $instructions[$i];
            //LOG.debugStartingInstruction(processInstance.getId(), i, instruction.describe());

            $instruction->setProcessInstanceId($processInstance->getId());
            $instruction->setSkipCustomListeners($modificationBuilder->isSkipCustomListeners());
            $instruction->setSkipIoMappings($modificationBuilder->isSkipIoMappings());
            $instruction->execute($commandContext);
        }

        if (!$processInstance->hasChildren() && $processInstance->isEnded()) {
            // process instance has ended regularly but this has not been propagated yet
            // due to preserveScope setting
            $processInstance->propagateEnd();
        }

        $commandContext->getOperationLogManager()->logProcessInstanceOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_CREATE,
            $processInstance->getId(),
            $processInstance->getProcessDefinitionId(),
            $processInstance->getProcessDefinition()->getKey(),
            [PropertyChange::emptyChange()],
            $modificationBuilder->getAnnotation()
        );

        return new ProcessInstanceWithVariablesImpl($processInstance, $variablesListener->getVariables());
    }

    /**
     * get the activity that is started by the first instruction, if exists;
     * return null if the first instruction is a start-transition instruction
     */
    protected function determineFirstActivity(
        ProcessDefinitionImpl $processDefinition,
        ProcessInstanceModificationBuilderImpl $modificationBuilder
    ): ?ActivityImpl {
        $ops = $modificationBuilder->getModificationOperations();
        if (count($ops)) {
            $firstInstruction = $ops[0];

            if ($firstInstruction instanceof AbstractInstantiationCmd) {
                $instantiationInstruction = $firstInstruction;
                $targetElement = $instantiationInstruction->getTargetElement($processDefinition);

                EnsureUtil::ensureNotNull(
                    "Element '" . $instantiationInstruction->getTargetElementId() . "' does not exist in process " . $processDefinition->getId(),
                    "targetElement",
                    $targetElement
                );

                if ($targetElement instanceof ActivityImpl) {
                    return $targetElement;
                } elseif ($targetElement instanceof TransitionImpl) {
                    return $targetElement->getDestination();
                }
            }
        }

        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
