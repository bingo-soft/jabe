<?php

namespace Jabe\Impl\Cmd\Batch;

use Jabe\BadUserRequestException;
use Jabe\Authorization\BatchPermissions;
use Jabe\Batch\BatchInterface;
use Jabe\History\{
    HistoricProcessInstanceQueryInterface,
    UserOperationLogEntryInterface
};
use Jabe\Impl\{
    HistoricProcessInstanceQueryImpl,
    ProcessInstanceQueryImpl
};
use Jabe\Impl\Batch\{
    BatchConfiguration,
    BatchElementConfiguration
};
use Jabe\Impl\Batch\Builder\{
    BatchBuilder,
    OperationLogInstanceCountHandlerInterface
};
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\PropertyChange;
use Jabe\Impl\Util\{
    CollectionUtil,
    EnsureUtil
};

class DeleteHistoricProcessInstancesBatchCmd implements CommandInterface
{
    protected $deleteReason;
    protected $historicProcessInstanceIds;
    protected $historicProcessInstanceQuery;

    public function __construct(array $historicProcessInstanceIds, HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery, ?string $deleteReason)
    {
        $this->historicProcessInstanceIds = $historicProcessInstanceIds;
        $this->historicProcessInstanceQuery = $historicProcessInstanceQuery;
        $this->deleteReason = $deleteReason;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $elementConfiguration = $this->collectHistoricProcessInstanceIds($commandContext);

        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "historicProcessInstanceIds", $elementConfiguration->getIds());

        $scope = $this;
        return (new BatchBuilder($commandContext))
            ->type(BatchInterface::TYPE_HISTORIC_PROCESS_INSTANCE_DELETION)
            ->config($this->getConfiguration($elementConfiguration))
            ->permission(BatchPermissions::createBatchDeleteFinishedProcessInstances())
            ->operationLogHandler(new class ($scope) implements OperationLogInstanceCountHandlerInterface {
                private $scope;

                public function __construct($scope)
                {
                    $this->scope = $scope;
                }

                public function write(CommandContext $commandContext, int $instanceCount): void
                {
                    $op = $this->scope->writeUserOperationLog;
                    $op($commandContext, $instanceCount);
                }
            })
            ->build();
    }

    protected function collectHistoricProcessInstanceIds(CommandContext $commandContext): BatchElementConfiguration
    {
        $elementConfiguration = new BatchElementConfiguration();
        $processInstanceIds = $this->getHistoricProcessInstanceIds();
        if (!CollectionUtil::isEmpty($processInstanceIds)) {
            $query = new HistoricProcessInstanceQueryImpl();
            $query->processInstanceIds($processInstanceIds);
            $elementConfiguration->addDeploymentMappings(
                $commandContext->runWithoutAuthorization(function () use ($query) {
                    return $query->listDeploymentIdMappings();
                }),
                $processInstanceIds
            );
        }

        $processInstanceQuery = $this->historicProcessInstanceQuery;
        if ($processInstanceQuery !== null) {
            $elementConfiguration->addDeploymentMappings($processInstanceQuery->listDeploymentIdMappings());
        }

        return $elementConfiguration;
    }

    public function getHistoricProcessInstanceIds(): array
    {
        return $this->historicProcessInstanceIds;
    }

    public function writeUserOperationLog(CommandContext $commandContext, int $numInstances): void
    {
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("nrOfInstances", null, $numInstances);
        $propertyChanges[] = new PropertyChange("async", null, true);
        $propertyChanges[] = new PropertyChange("deleteReason", null, $this->deleteReason);

        $commandContext->getOperationLogManager()
            ->logProcessInstanceOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_HISTORY,
                null,
                null,
                null,
                $propertyChanges,
                null
            );
    }

    public function getConfiguration(BatchElementConfiguration $elementConfiguration): BatchConfiguration
    {
        return new BatchConfiguration($elementConfiguration->getIds(), $elementConfiguration->getMappings(), false);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
