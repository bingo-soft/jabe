<?php

namespace Jabe\Impl\Cmd;

use Jabe\Authorization\BatchPermissions;
use Jabe\Batch\BatchInterface;
use Jabe\Impl\UpdateProcessInstancesSuspensionStateBuilderImpl;
use Jabe\Impl\Batch\Builder\{
    BatchBuilder,
    OperationLogInstanceCountHandlerInterface
};
use Jabe\Impl\Batch\{
    BatchConfiguration,
    BatchElementConfiguration
};
use Jabe\Impl\Batch\Update\UpdateProcessInstancesSuspendStateBatchConfiguration;
use Jabe\Impl\Interceptor\{
    CommandExecutorInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class UpdateProcessInstancesSuspendStateBatchCmd extends AbstractUpdateProcessInstancesSuspendStateCmd
{
    public function __construct(
        CommandExecutorInterface $commandExecutor,
        UpdateProcessInstancesSuspensionStateBuilderImpl $builder,
        bool $suspending
    ) {
        parent::__construct($commandExecutor, $builder, $suspending);
    }

    public function execute(CommandContext $commandContext)
    {
        $elementConfiguration = $this->collectProcessInstanceIds($commandContext);

        EnsureUtil::ensureNotEmpty("No process instance ids given", "process Instance Ids", $elementConfiguration->getIds());
        EnsureUtil::ensureNotContainsNull("Cannot be null.", "Process Instance ids", $elementConfiguration->getIds());

        $scope = $this;
        return (new BatchBuilder($commandContext))
            ->type(BatchInterface::TYPE_PROCESS_INSTANCE_UPDATE_SUSPENSION_STATE)
            ->config($this->getConfiguration($elementConfiguration))
            ->permission(BatchPermissions::createBatchUpdateProcessInstancesSuspend())
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
        return new UpdateProcessInstancesSuspendStateBatchConfiguration(
            $elementConfiguration->getIds(),
            $elementConfiguration->getMappings(),
            $this->suspending
        );
    }
}
