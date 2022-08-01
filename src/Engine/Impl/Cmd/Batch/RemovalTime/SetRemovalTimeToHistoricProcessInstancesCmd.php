<?php

namespace Jabe\Engine\Impl\Cmd\Batch\RemovalTime;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Authorization\BatchPermissions;
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\History\{
    HistoricProcessInstanceQueryInterface,
    UserOperationLogEntryInterface
};
use Jabe\Engine\Impl\Batch\{
    BatchConfiguration,
    BatchElementConfiguration
};
use Jabe\Engine\Impl\Batch\Builder\{
    BatchBuilder,
    OperationLogInstanceCountHandlerInterface
};
use Jabe\Engine\Impl\HistoricProcessInstanceQueryImpl;
use Jabe\Engine\Impl\Batch\RemovalTime\SetRemovalTimeBatchConfiguration;
use Jabe\Engine\Impl\History\{
    SetRemovalTimeToHistoricProcessInstancesBuilderImpl,
    Mode
};
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\PropertyChange;
use Jabe\Engine\Impl\Util\{
    CollectionUtil,
    EnsureUtil
};

class SetRemovalTimeToHistoricProcessInstancesCmd implements CommandInterface
{
    protected $builder;

    public function __construct(SetRemovalTimeToHistoricProcessInstancesBuilderImpl $builder)
    {
        $this->builder = $builder;
    }

    public function execute(CommandContext $commandContext)
    {
        if ($this->builder->getQuery() === null && $this->builder->getIds() === null) {
            throw new BadUserRequestException("Neither query nor ids provided.");
        }

        $elementConfiguration = $this->collectInstances($commandContext);

        EnsureUtil::ensureNotNull(BadUserRequestException::class, "removalTime", $this->builder->getMode());
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "historicProcessInstances", $elementConfiguration->getIds());

        $scope = $this;
        return (new BatchBuilder($commandContext))
            ->type(BatchInterface::TYPE_PROCESS_SET_REMOVAL_TIME)
            ->config($this->getConfiguration($elementConfiguration))
            ->permission(BatchPermissions::createBatchSetRemovalTime())
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

    protected function collectInstances(CommandContext $commandContext): BatchElementConfiguration
    {
        $elementConfiguration = new BatchElementConfiguration();
        $instanceQuery = $this->builder->getQuery();
        if ($instanceQuery !== null) {
            $elementConfiguration->addDeploymentMappings($instanceQuery->listDeploymentIdMappings());
        }

        $instanceIds = $this->builder->getIds();
        if (!CollectionUtil::isEmpty($instanceIds)) {
            $query = new HistoricProcessInstanceQueryImpl();
            $query->processInstanceIds($instanceIds);
            $elementConfiguration->addDeploymentMappings($commandContext->runWithoutAuthorization(function () use ($query) {
                return $query->listDeploymentIdMappings();
            }));
        }
        return $elementConfiguration;
    }

    protected function getConfiguration(BatchElementConfiguration $elementConfiguration): BatchConfiguration
    {
        return (new SetRemovalTimeBatchConfiguration($elementConfiguration->getIds(), $elementConfiguration->getMappings()))
            ->setHierarchical($this->builder->isHierarchical())
            ->setHasRemovalTime($this->hasRemovalTime())
            ->setRemovalTime($this->builder->getRemovalTime());
    }

    protected function hasRemovalTime(): bool
    {
        return $this->builder->getMode() == Mode::ABSOLUTE_REMOVAL_TIME ||
            $this->builder->getMode() == Mode::CLEARED_REMOVAL_TIME;
    }

    public function writeUserOperationLog(CommandContext $commandContext, int $numInstances): void
    {
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("mode", null, $this->builder->getMode());
        $propertyChanges[] = new PropertyChange("removalTime", null, $this->builder->getRemovalTime());
        $propertyChanges[] = new PropertyChange("hierarchical", null, $this->builder->isHierarchical());
        $propertyChanges[] = new PropertyChange("nrOfInstances", null, $numInstances);
        $propertyChanges[] = new PropertyChange("async", null, true);

        $commandContext->getOperationLogManager()
            ->logProcessInstanceOperation(UserOperationLogEntryInterface::OPERATION_TYPE_SET_REMOVAL_TIME, $propertyChanges);
    }
}
