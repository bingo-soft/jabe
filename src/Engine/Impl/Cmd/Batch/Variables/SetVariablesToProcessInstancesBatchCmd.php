<?php

namespace Jabe\Engine\Impl\Cmd\Batch\Variables;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Authorization\BatchPermissions;
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\History\{
    HistoricProcessInstanceQueryInterface,
    UserOperationLogEntryInterface
};
use Jabe\Engine\Impl\{
    HistoricProcessInstanceQueryImpl,
    ProcessInstanceQueryImpl
};
use Jabe\Engine\Impl\Batch\{
    BatchConfiguration,
    BatchElementConfiguration,
    DeploymentMappings
};
use Jabe\Engine\Impl\Batch\Builder\{
    BatchBuilder,
    OperationLogInstanceCountHandlerInterface
};
use Jabe\Engine\Impl\Batch\Message\MessageCorrelationBatchConfiguration;
use Jabe\Engine\Impl\Core\Variable\Util\VariableUtil;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\PropertyChange;
use Jabe\Engine\Impl\Util\{
    CollectionUtil,
    EnsureUtil,
    ImmutablePair
};
use Jabe\Engine\Runtime\ProcessInstanceQueryInterface;

class SetVariablesToProcessInstancesBatchCmd implements CommandInterface
{
    protected $processInstanceIds;
    protected $processInstanceQuery;
    protected $historicProcessInstanceQuery;
    protected $variables;

    public function __construct(
        array $processInstanceIds,
        ProcessInstanceQueryInterface $processInstanceQuery,
        HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery,
        array $variables
    ) {
        $this->processInstanceIds = $processInstanceIds;
        $this->processInstanceQuery = $processInstanceQuery;
        $this->historicProcessInstanceQuery = $historicProcessInstanceQuery;
        $this->variables = $variables;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("variables", "variables", $this->variables);
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "variables", $this->variables);
        EnsureUtil::ensureAtLeastOneNotNull(
            "No process instances found.",
            $this->processInstanceIds,
            $this->processInstanceQuery,
            $this->historicProcessInstanceQuery
        );

        $elementConfiguration = $this->collectProcessInstanceIds($commandContext);

        $ids = $elementConfiguration->getIds();
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "processInstanceIds", $ids);

        $configuration = $this->getConfiguration($elementConfiguration);
        $scope = $this;
        $batch = (new BatchBuilder($commandContext))
            ->type(BatchInterface::TYPE_SET_VARIABLES)
            ->config($configuration)
            ->permission(BatchPermissions::createBatchSetVariables())
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

        $batchId = $batch->getId();
        VariableUtil::setVariablesByBatchId($this->variables, $batchId);

        return $batch;
    }

    public function writeUserOperationLog(CommandContext $commandContext, int $instancesCount): void
    {
        $propChanges = [];
        $variablesCount = count($this->variables);
        $propChanges[] = new PropertyChange("nrOfInstances", null, $instancesCount);
        $propChanges[] = new PropertyChange("nrOfVariables", null, $variablesCount);
        $propChanges[] = new PropertyChange("async", null, true);

        $commandContext->getOperationLogManager()
            ->logProcessInstanceOperation(UserOperationLogEntryInterface::OPERATION_TYPE_SET_VARIABLES, $propChanges);
    }

    public function getConfiguration(BatchElementConfiguration $elementConfiguration): BatchConfiguration
    {
        $mappings = $elementConfiguration->getMappings();
        $ids = $elementConfiguration->getIds();
        return new BatchConfiguration($ids, $mappings);
    }

    protected function collectProcessInstanceIds(CommandContext $commandContext): BatchElementConfiguration
    {
        $elementConfiguration = new BatchElementConfiguration();

        if (!CollectionUtil::isEmpty($processInstanceIds)) {
            $query = new ProcessInstanceQueryImpl();
            $query->processInstanceIds($processInstanceIds);
            $mappings = $commandContext->runWithoutAuthorization(function () use ($query) {
                return $query->listDeploymentIdMappings();
            });
            $elementConfiguration->addDeploymentMappings($mappings);
        }

        $processInstanceQuery = $this->processInstanceQuery;
        if ($processInstanceQuery != null) {
            $mappings = $processInstanceQuery->listDeploymentIdMappings();
            $elementConfiguration->addDeploymentMappings($mappings);
        }

        $historicProcessInstanceQuery = $this->historicProcessInstanceQuery;
        if ($historicProcessInstanceQuery != null) {
            $historicProcessInstanceQuery->unfinished();
            $mappings = $historicProcessInstanceQuery->listDeploymentIdMappings();
            $elementConfiguration->addDeploymentMappings($mappings);
        }
        return $elementConfiguration;
    }
}
