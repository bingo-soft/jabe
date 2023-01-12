<?php

namespace Jabe\Impl\Batch;

use Jabe\BatchPermissions;
use Jabe\Batch\BatchInterface;
use Jabe\Impl\{
    ProcessEngineLogger,
    RestartProcessInstanceBuilderImpl,
    RestartProcessInstancesBatchConfiguration
};
use Jabe\Impl\Batch\Builder\{
    BatchBuilder,
    OperationLogInstanceCountHandlerInterface
};
use Jabe\Impl\Cmd\{
    AbstractProcessInstanceModificationCommand,
    AbstractRestartProcessInstanceCmd,
    CommandLogger
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Persistence\Entity\ProcessDefinitionEntity;
use Jabe\Impl\Util\EnsureUtil;

class RestartProcessInstancesBatchCmd extends AbstractRestartProcessInstanceCmd
{
    //private final CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    public function __construct(CommandExecutorInterface $commandExecutor, RestartProcessInstanceBuilderImpl $builder)
    {
        parent::__construct($commandExecutor, $builder);
    }

    public function execute(CommandContext $commandContext)
    {
        $collectedInstanceIds = $this->collectProcessInstanceIds();

        $instructions = $this->builder->getInstructions();
        EnsureUtil::ensureNotEmpty("Restart instructions cannot be empty", "instructions", $instructions);
        EnsureUtil::ensureNotEmpty("Process instance ids cannot be empty", "processInstanceIds", $collectedInstanceIds);
        EnsureUtil::ensureNotContainsNull("Process instance ids cannot be null", "processInstanceIds", $collectedInstanceIds);

        $processDefinitionId = $this->builder->getProcessDefinitionId();
        $processDefinition = $this->getProcessDefinition($commandContext, $processDefinitionId);

        EnsureUtil::ensureNotNull("Process definition cannot be null", "processDefinition", $processDefinition);
        EnsureUtil::ensureTenantAuthorized($commandContext, $processDefinition);

        $tenantId = $processDefinition->getTenantId();

        $scope = $this;
        return (new BatchBuilder($commandContext))
            ->type(BatchInterface::TYPE_PROCESS_INSTANCE_RESTART)
            ->config(getConfiguration($collectedInstanceIds, $processDefinition->getDeploymentId()))
            ->permission(BatchPermissions::createBatchRestartProcessInstances())
            ->tenantId($tenantId)
            ->operationLogHandler(new class ($scope, $processDefinition) implements OperationLogInstanceCountHandlerInterface {
                private $scope;
                private $processDefinition;

                public function __construct($scope, $processDefinition)
                {
                    $this->scope = $scope;
                    $this->processDefinition = $processDefinition;
                }

                public function write(CommandContext $commandContext, int $instanceCount): void
                {
                    $op = $this->scope->writeUserOperationLog;
                    $op($commandContext, $this->processDefinition, $instanceCount, true);
                }
            })
            ->build();
    }

    protected function ensureTenantAuthorized(CommandContext $commandContext, ProcessDefinitionEntity $processDefinition): void
    {
        if (!$commandContext->getTenantManager()->isAuthenticatedTenant($processDefinition->getTenantId())) {
            //throw LOG.exceptionCommandWithUnauthorizedTenant("restart process instances of process definition '" + processDefinition.getId() + "'");
            throw new \Exception("restart process instances of process definition '" . $processDefinition->getId() . "'");
        }
    }

    public function getConfiguration(array $instanceIds, ?string $deploymentId): BatchConfiguration
    {
        return new RestartProcessInstancesBatchConfiguration(
            $instanceIds,
            DeploymentMappings::of(new DeploymentMapping($deploymentId, count($instanceIds))),
            $this->builder->getInstructions(),
            $this->builder->getProcessDefinitionId(),
            $this->builder->isInitialVariables(),
            $this->builder->isSkipCustomListeners(),
            $this->builder->isSkipIoMappings(),
            $this->builder->isWithoutBusinessKey()
        );
    }
}
