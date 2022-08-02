<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Authorization\BatchPermissions;
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Batch\{
    BatchConfiguration,
    BatchElementConfiguration,
    SetRetriesBatchConfiguration
};
use Jabe\Engine\Impl\Batch\Builder\{
    BatchBuilder,
    OperationLogInstanceCountHandlerInterface
};
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\PropertyChange;
use Jabe\Engine\Impl\Util\EnsureUtil;

abstract class AbstractSetJobsRetriesBatchCmd implements CommandInterface
{
    protected $retries;

    public function execute(CommandContext $commandContext)
    {
        $elementConfiguration = $this->collectJobIds($commandContext);

        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "jobIds", $elementConfiguration->getIds());
        EnsureUtil::ensureGreaterThanOrEqual("Number of retries cannot be negative", "Retries count", $this->retries, 0);
        $scope = new stdClass();
        $scope->write = function (CommandContext $commandContext, int $instanceCount) {
            $this->writeUserOperationLog($commandContext, $instanceCount);
        };
        return (new BatchBuilder($commandContext))
            ->config($this->getConfiguration($elementConfiguration))
            ->type(BatchInterface::TYPE_SET_JOB_RETRIES)
            ->permission(BatchPermissions::createBatchSetJobRetries())
            ->operationLogHandler(new class ($scope) implements OperationLogInstanceCountHandlerInterface {
                private $scope;

                public function __construct($scope)
                {
                    $this->scope = $scope;
                }

                public function write(CommandContext $commandContext, int $instanceCount): void
                {
                    $op = $this->scope->write;
                    $op($commandContext, $instanceCount);
                }
            })
            ->build();
    }

    public function writeUserOperationLog(CommandContext $commandContext, int $numInstances): void
    {
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange(
            "nrOfInstances",
            null,
            $numInstances
        );
        $propertyChanges[] = new PropertyChange("async", null, true);
        $propertyChanges[] = new PropertyChange("retries", null, $this->retries);

        $commandContext->getOperationLogManager()
            ->logJobOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_SET_JOB_RETRIES,
                null,
                null,
                null,
                null,
                null,
                $propertyChanges
            );
    }

    abstract protected function collectJobIds(CommandContext $commandContext): BatchElementConfiguration;

    public function getConfiguration(BatchElementConfiguration $elementConfiguration): BatchConfiguration
    {
        return new SetRetriesBatchConfiguration($elementConfiguration->getIds(), $elementConfiguration->getMappings(), $this->retries);
    }
}
