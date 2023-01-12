<?php

namespace Jabe\Impl\Cmd\Batch;

use Jabe\Authorization\BatchPermissions;
use Jabe\Batch\BatchInterface;
use Jabe\History\{
    HistoricProcessInstanceQueryInterface,
    UserOperationLogEntryInterface
};
use Jabe\Impl\{
    HistoricProcessInstanceQueryImpl,
    MessageCorrelationAsyncBuilderImpl,
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
use Jabe\Impl\Batch\Message\MessageCorrelationBatchConfiguration;
use Jabe\Impl\Core\Variable\Util\VariableUtil;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\PropertyChange;
use Jabe\Impl\Util\{
    CollectionUtil,
    EnsureUtil
};

class CorrelateAllMessageBatchCmd implements CommandInterface
{
    protected $messageName;
    protected $variables;
    protected $processInstanceIds;
    protected $processInstanceQuery;
    protected $historicProcessInstanceQuery;

    public function __construct(MessageCorrelationAsyncBuilderImpl $asyncBuilder)
    {
        $this->messageName = $asyncBuilder->getMessageName();
        $this->variables = $asyncBuilder->getPayloadProcessInstanceVariables();
        $this->processInstanceIds = $asyncBuilder->getProcessInstanceIds();
        $this->processInstanceQuery = $asyncBuilder->getProcessInstanceQuery();
        $this->historicProcessInstanceQuery = $asyncBuilder->getHistoricProcessInstanceQuery();
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureAtLeastOneNotNull(
            "No process instances found.",
            $this->processInstanceIds,
            $this->processInstanceQuery,
            $this->historicProcessInstanceQuery
        );

        $elementConfiguration = $this->collectProcessInstanceIds($commandContext);

        $ids = $elementConfiguration->getIds();
        EnsureUtil::ensureNotEmpty("Process instance ids cannot be empty", "process instance ids", $ids);

        $scope = $this;
        $batch = (new BatchBuilder($commandContext))
            ->type(BatchInterface::TYPE_CORRELATE_MESSAGE)
            ->config($this->getConfiguration($elementConfiguration))
            ->permission(BatchPermissions::createBatchCorrelateMessage())
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

        if (!empty($this->variables)) {
            VariableUtil::setVariablesByBatchId($this->variables, $batch->getId());
        }

        return $batch;
    }

    protected function collectProcessInstanceIds(CommandContext $commandContext): BatchElementConfiguration
    {
        $elementConfiguration = new BatchElementConfiguration();

        if (!CollectionUtil::isEmpty($this->processInstanceIds)) {
            $query = new ProcessInstanceQueryImpl();
            $query->processInstanceIds($this->processInstanceIds);

            $elementConfiguration->addDeploymentMappings(
                $commandContext->runWithoutAuthorization(function () use ($query) {
                    return $query->listDeploymentIdMappings();
                }),
                $this->processInstanceIds
            );
        }

        if ($this->processInstanceQuery !== null) {
            $elementConfiguration->addDeploymentMappings($this->processInstanceQuery->listDeploymentIdMappings());
        }

        if ($this->historicProcessInstanceQuery !== null) {
            $elementConfiguration->addDeploymentMappings($historicProcessInstanceQuery->listDeploymentIdMappings());
        }

        return $elementConfiguration;
    }

    protected function getConfiguration(BatchElementConfiguration $elementConfiguration): BatchConfiguration
    {
        return new MessageCorrelationBatchConfiguration(
            $elementConfiguration->getIds(),
            $elementConfiguration->getMappings(),
            $this->messageName
        );
    }

    public function writeUserOperationLog(CommandContext $commandContext, int $instancesCount): void
    {
        $propChanges = [];

        $propChanges[] = new PropertyChange("messageName", null, $this->messageName);
        $propChanges[] = new PropertyChange("nrOfInstances", null, $instancesCount);
        $propChanges[] = new PropertyChange("nrOfVariables", null, empty($this->variables) ? 0 : count($this->variables));
        $propChanges[] = new PropertyChange("async", null, true);

        $commandContext->getOperationLogManager()
            ->logProcessInstanceOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_CORRELATE_MESSAGE,
                null,
                null,
                null,
                $propChanges,
                null
            );
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
