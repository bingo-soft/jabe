<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\{
    HistoricProcessInstanceQueryImpl,
    ProcessInstanceQueryImpl,
    UpdateProcessInstancesSuspensionStateBuilderImpl
};
use Jabe\Engine\Impl\Batch\BatchElementConfiguration;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Persistence\Entity\PropertyChange;
use Jabe\Engine\Impl\Util\{
    CollectionUtil,
    EnsureUtil
};

abstract class AbstractUpdateProcessInstancesSuspendStateCmd implements CommandInterface
{
    protected $builder;
    protected $commandExecutor;
    protected $suspending;

    public function __construct(CommandExecutorInterface $commandExecutor, UpdateProcessInstancesSuspensionStateBuilderImpl $builder, bool $suspending)
    {
        $this->commandExecutor = $commandExecutor;
        $this->builder = $builder;
        $this->suspending = $suspending;
    }

    protected function collectProcessInstanceIds(CommandContext $commandContext): BatchElementConfiguration
    {
        $elementConfiguration = new BatchElementConfiguration();

        $processInstanceIds = $builder->getProcessInstanceIds();
        EnsureUtil::ensureNotContainsNull(
            "Cannot be null.",
            "Process Instance ids",
            $processInstanceIds
        );
        if (!CollectionUtil::isEmpty($processInstanceIds)) {
            $query = new ProcessInstanceQueryImpl();
            $query->processInstanceIds($processInstanceIds);
            $elementConfiguration->addDeploymentMappings(
                $commandContext->runWithoutAuthorization(function () use ($query) {
                    return $query->listDeploymentIdMappings();
                }),
                $processInstanceIds
            );
        }

        $processInstanceQuery = $builder->getProcessInstanceQuery();
        if ($processInstanceQuery !== null) {
            $elementConfiguration->addDeploymentMappings($processInstanceQuery->listDeploymentIdMappings());
        }

        $historicProcessInstanceQuery = $builder->getHistoricProcessInstanceQuery();
        if ($historicProcessInstanceQuery !== null) {
            $elementConfiguration->addDeploymentMappings($historicProcessInstanceQuery->listDeploymentIdMappings());
        }

        return $elementConfiguration;
    }

    public function writeUserOperationLog(
        CommandContext $commandContext,
        int $numInstances,
        bool $async
    ): void {
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("nrOfInstances", null, $numInstances);
        $propertyChanges[] = new PropertyChange("async", null, $async);

        $operationType = null;
        if ($this->suspending) {
            $operationType = UserOperationLogEntryInterface::OPERATION_TYPE_SUSPEND_JOB;
        } else {
            $operationType = UserOperationLogEntryInterface::OPERATION_TYPE_ACTIVATE_JOB;
        }
        $commandContext->getOperationLogManager()
            ->logProcessInstanceOperation(
                $operationType,
                null,
                null,
                null,
                $propertyChanges
            );
    }

    public function writeUserOperationLogAsync(CommandContext $commandContext, int $numInstances): void
    {
        $this->writeUserOperationLog($commandContext, $numInstances, true);
    }
}
