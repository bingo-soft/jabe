<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Authorization\BatchPermissions;
use BpmPlatform\Engine\Batch\BatchInterface;
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\{
    ModificationBatchConfiguration,
    ProcessEngineLogger,
    ProcessInstanceModificationBuilderImpl
};
use BpmPlatform\Engine\Impl\Batch\Builder\{
    BatchBuilder,
    OperationLogHandlerInterface
};
use BpmPlatform\Engine\Impl\Batch\{
    BatchConfiguration,
    DeploymentMapping,
    DeploymentMappings
};
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    PropertyChange
};

class ModifyProcessInstanceAsyncCmd implements CommandInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $builder;

    public function __construct(ProcessInstanceModificationBuilderImpl $builder)
    {
        $this->builder = $builder;
    }

    public function execute(CommandContext $commandContext)
    {
        $processInstanceId = $this->builder->getProcessInstanceId();

        $executionManager = $commandContext->getExecutionManager();
        $processInstance = $executionManager->findExecutionById($processInstanceId);

        $this->ensureProcessInstanceExists($processInstanceId, $processInstance);

        $processDefinitionId = $processInstance->getProcessDefinitionId();
        $tenantId = $processInstance->getTenantId();

        $deploymentId = $commandContext->getProcessEngineConfiguration()->getDeploymentCache()
            ->findDeployedProcessDefinitionById($processDefinitionId)
            ->getDeploymentId();

        $scope = $this;
        return (new BatchBuilder($commandContext))
            ->type(BatchInterface::TYPE_PROCESS_INSTANCE_MODIFICATION)
            ->config($this->getConfiguration($processDefinitionId, $deploymentId))
            ->tenantId($tenantId)
            ->totalJobs(1)
            ->permission(BatchPermissions::createBatchModifyProcessInstances())
            ->operationLogHandler(new class ($scope) implements OperationLogHandlerInterface {
                private $scope;

                public function __construct($scope)
                {
                    $this->scope = $scope;
                }

                public function write(CommandContext $commandContext): void
                {
                    $op = $this->scope->writeOperationLog;
                    $op($commandContext);
                }
            })
            ->build();
    }

    protected function ensureProcessInstanceExists(string $processInstanceId, ?ExecutionEntity $processInstance): void
    {
        if ($processInstance == null) {
            //throw LOG.processInstanceDoesNotExist(processInstanceId);
            throw new \Exception("processInstanceDoesNotExist $processInstanceId");
        }
    }

    protected function getLogEntryOperation(): string
    {
        return UserOperationLogEntryInterface::OPERATION_TYPE_MODIFY_PROCESS_INSTANCE;
    }

    protected function writeOperationLog(CommandContext $commandContext): void
    {
        $commandContext->getOperationLogManager()->logProcessInstanceOperation(
            $this->getLogEntryOperation(),
            $this->builder->getProcessInstanceId(),
            null,
            null,
            [PropertyChange::emptyChange()],
            $this->builder->getAnnotation()
        );
    }

    public function getConfiguration(string $processDefinitionId, string $deploymentId): BatchConfiguration
    {
        return new ModificationBatchConfiguration(
            [$this->builder->getProcessInstanceId()],
            DeploymentMappings::of(new DeploymentMapping($deploymentId, 1)),
            $processDefinitionId,
            $this->builder->getModificationOperations(),
            $this->builder->isSkipCustomListeners(),
            $this->builder->isSkipIoMappings()
        );
    }
}
