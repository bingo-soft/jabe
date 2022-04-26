<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Authorization\BatchPermissions;
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Impl\Batch\Builder\{
    BatchBuilder,
    OperationLogInstanceCountHandlerInterface
};
use Jabe\Engine\Impl\Batch\{
    BatchConfiguration,
    BatchElementConfiguration,
    SetRetriesBatchConfiguration
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Util\EnsureUtil;

class SetExternalTasksRetriesBatchCmd extends AbstractSetExternalTaskRetriesCmd
{
    public function __construct(UpdateExternalTaskRetriesBuilderImpl $builder)
    {
        parent::__construct($builder);
    }

    public function execute(CommandContext $commandContext)
    {
        $elementConfiguration = $this->collectExternalTaskIds($commandContext);

        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "externalTaskIds", $elementConfiguration->getIds());

        $scope = $this;
        return (new BatchBuilder($commandContext))
            ->type(BatchInterface::TYPE_SET_EXTERNAL_TASK_RETRIES)
            ->config($this->getConfiguration($elementConfiguration))
            ->permission(BatchPermissions::reateBatchSetExternalTaskRetries())
            ->operationLogHandler(new class ($scope) implements OperationLogInstanceCountHandlerInterface {
                private $scope;

                public function __construct($scope)
                {
                    $this->scope = $scope;
                }

                public function write(CommandContext $commandContext, int $instanceCount): void
                {
                    $op = $this->scope->writeUserOperationLogAsync;
                    $op($commandContext, $instanceCount);
                }
            })
            ->build();
    }

    public function getConfiguration(BatchElementConfiguration $elementConfiguration): BatchConfiguration
    {
        return new SetRetriesBatchConfiguration(
            $elementConfiguration->getIds(),
            $elementConfiguration->getMappings(),
            $this->builder->getRetries()
        );
    }
}
