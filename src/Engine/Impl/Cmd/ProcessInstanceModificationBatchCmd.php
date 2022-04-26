<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Authorization\BatchPermissions;
use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Impl\{
    ModificationBatchConfiguration,
    ModificationBuilderImpl
};
use Jabe\Engine\Impl\Batch\Builder\{
    BatchBuilder,
    OperationLogInstanceCountHandlerInterface
};
use Jabe\Engine\Impl\Batch\{
    BatchConfiguration,
    DeploymentMapping,
    DeploymentMappings
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Util\EnsureUtil;

class ProcessInstanceModificationBatchCmd extends AbstractModificationCmd
{
    public function __construct(ModificationBuilderImpl $modificationBuilderImpl)
    {
        parent::__construct($modificationBuilderImpl);
    }

    public function execute(CommandContext $commandContext)
    {
        $instructions = $this->builder->getInstructions();
        EnsureUtil::ensureNotEmpty("Modification instructions cannot be empty", "instructions", $instructions);

        $collectedInstanceIds = $this->collectProcessInstanceIds();

        EnsureUtil::ensureNotEmpty(
            "Process instance ids cannot be empty",
            "Process instance ids",
            $collectedInstanceIds
        );

        EnsureUtil::ensureNotContainsNull(
            "Process instance ids cannot be null",
            "Process instance ids",
            $collectedInstanceIds
        );

        $processDefinitionId = $this->builder->getProcessDefinitionId();
        $processDefinition = $this->getProcessDefinition($commandContext, $processDefinitionId);

        EnsureUtil::ensureNotNull("Process definition id cannot be null", "processDefinition", $processDefinition);

        $tenantId = $processDefinition->getTenantId();
        $annotation = $this->builder->getAnnotation();

        $scope = $this;
        return (new BatchBuilder($commandContext))
            ->type(BatchInterface::TYPE_PROCESS_INSTANCE_MODIFICATION)
            ->config($this->getConfiguration($collectedInstanceIds, $processDefinition->getDeploymentId()))
            ->tenantId($tenantId)
            ->permission(BatchPermissions::createBatchModifyProcessInstances())
            ->operationLogHandler(new class ($scope, $processDefinition, $annotation) implements OperationLogInstanceCountHandlerInterface {
                private $scope;
                private $processDefinition;
                private $annotation;

                public function __construct($scope, $processDefinition, $annotation)
                {
                    $this->scope = $scope;
                    $this->processDefinition = $processDefinition;
                    $this->annotation = $annotation;
                }

                public function write(CommandContext $commandContext, int $instanceCount): void
                {
                    $op = $this->scope->writeUserOperationLog;
                    $op($commandContext, $this->processDefinition, $instanceCount, true, $this->annotation);
                }
            })
            ->build();
    }

    public function getConfiguration(array $instanceIds, string $deploymentId): BatchConfiguration
    {
        return new ModificationBatchConfiguration(
            $instanceIds,
            DeploymentMappings::of(new DeploymentMapping($deploymentId, count($instanceIds))),
            $this->builder->getProcessDefinitionId(),
            $this->builder->getInstructions(),
            $this->builder->isSkipCustomListeners(),
            $this->builder->isSkipIoMappings()
        );
    }
}
