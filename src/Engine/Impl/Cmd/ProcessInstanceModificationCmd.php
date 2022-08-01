<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\{
    ModificationBuilderImpl,
    ProcessEngineLogger,
    ProcessInstanceModificationBuilderImpl
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;

class ProcessInstanceModificationCmd extends AbstractModificationCmd
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;
    protected $writeUserOperationLog;

    public function __construct(ModificationBuilderImpl $builder, bool $writeUserOperationLog)
    {
        parent::__construct($builder);
        $this->writeUserOperationLog = $writeUserOperationLog;
    }

    public function execute(CommandContext $commandContext)
    {
        $instructions = $this->builder->getInstructions();
        EnsureUtil::ensureNotEmpty("Modification instructions cannot be empty", "instructions", $instructions);

        $processInstanceIds = $this->collectProcessInstanceIds();
        EnsureUtil::ensureNotEmpty("Process instance ids cannot be empty", "Process instance ids", $processInstanceIds);

        EnsureUtil::ensureNotContainsNull("Process instance ids cannot be null", "Process instance ids", $processInstanceIds);

        $processDefinition = $this->getProcessDefinition($commandContext, $this->builder->getProcessDefinitionId());

        EnsureUtil::ensureNotNull("Process definition id cannot be null", "processDefinition", $processDefinition);

        if ($this->writeUserOperationLog) {
            $annotation = $this->builder->getAnnotation();
            $this->writeUserOperationLog($commandContext, $processDefinition, count($processInstanceIds), false, $annotation);
        }

        $skipCustomListeners = $this->builder->isSkipCustomListeners();
        $skipIoMappings = $this->builder->isSkipIoMappings();

        foreach ($processInstanceIds as $processInstanceId) {
            $processInstance = $commandContext->getExecutionManager()
                ->findExecutionById($processInstanceId);

            $this->ensureProcessInstanceExist($processInstanceId, $processInstance);
            $this->ensureSameProcessDefinition($processInstance, $processDefinition->getId());

            $builder = $this->createProcessInstanceModificationBuilder($processInstanceId, $commandContext);
            $builder->execute(false, $skipCustomListeners, $skipIoMappings);
        }

        return null;
    }

    protected function ensureSameProcessDefinition(ExecutionEntity $processInstance, string $processDefinitionId): void
    {
        if ($processDefinitionId != $processInstance->getProcessDefinitionId()) {
            //throw LOG.processDefinitionOfInstanceDoesNotMatchModification(processInstance,
            //    processDefinitionId);
            throw new \Exception("processDefinitionOfInstanceDoesNotMatchModification - $processDefinitionId");
        }
    }

    protected function ensureProcessInstanceExist(string $processInstanceId, ExecutionEntity $processInstance): void
    {
        if ($processInstance === null) {
            //throw LOG.processInstanceDoesNotExist(processInstanceId);
            throw new \Exception("processInstanceDoesNotExist($processInstanceId)");
        }
    }

    protected function createProcessInstanceModificationBuilder(string $processInstanceId, CommandContext $commandContext): ProcessInstanceModificationBuilderImpl
    {

        $processInstanceModificationBuilder = new ProcessInstanceModificationBuilderImpl($commandContext, $processInstanceId);

        $operations = $processInstanceModificationBuilder->getModificationOperations();

        $activityInstanceTree = null;

        foreach ($this->builder->getInstructions() as $instruction) {
            $instruction->setProcessInstanceId($processInstanceId);

            if (
                !($instruction instanceof ActivityCancellationCmd) ||
                !$instruction->isCancelCurrentActiveActivityInstances()
            ) {
                $processInstanceModificationBuilder->addModificationOperation($instruction);
            } else {
                if ($activityInstanceTree === null) {
                    $activityInstanceTree = $commandContext->runWithoutAuthorization(function () use ($commandContext, $processInstanceId) {
                        $cmd = new GetActivityInstanceCmd($processInstanceId);
                        return $cmd->execute($commandContext);
                    });
                }

                $cancellationInstruction = $instruction;
                $cmds = $cancellationInstruction->createActivityInstanceCancellations($activityInstanceTree, $commandContext);

                foreach ($cmds as $cmd) {
                    $processInstanceModificationBuilder->addModificationOperation($cmd);
                }
            }
        }

        return $processInstanceModificationBuilder;
    }
}
