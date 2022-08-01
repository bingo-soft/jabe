<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\ActivityExecutionTreeMapping;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;

class ActivityInstanceCancellationCmd extends AbstractInstanceCancellationCmd
{
    protected $activityInstanceId;

    public function __construct(string $processInstanceId, string $activityInstanceId, ?string $cancellationReason = null)
    {
        parent::__construct($processInstanceId, $cancellationReason);
        $this->activityInstanceId = $activityInstanceId;
    }

    public function getActivityInstanceId(): string
    {
        return $this->activityInstanceId;
    }

    protected function determineSourceInstanceExecution(CommandContext $commandContext): ExecutionEntity
    {
        $processInstance = $commandContext->getExecutionManager()->findExecutionById($this->processInstanceId);

        // rebuild the mapping because the execution tree changes with every iteration
        $mapping = new ActivityExecutionTreeMapping($commandContext, $this->processInstanceId);

        $scope = $this;
        $instance = $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext) {
            $cmd = new GetActivityInstanceCmd($scope->processInstanceId);
            $cmd->execute($commandContext);
        });

        $instanceToCancel = $this->findActivityInstance($instance, $this->activityInstanceId);
        EnsureUtil::ensureNotNull(
            describeFailure("Activity instance '" . $this->activityInstanceId . "' does not exist"),
            "activityInstance",
            $instanceToCancel
        );
        $scopeExecution = $this->getScopeExecutionForActivityInstance($processInstance, $mapping, $instanceToCancel);

        return $scopeExecution;
    }

    protected function describe(): string
    {
        return "Cancel activity instance '" . $this->activityInstanceId . "'";
    }
}
