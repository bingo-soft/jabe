<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Exception\NotValidException;
use BpmPlatform\Engine\Impl\ActivityExecutionTreeMapping;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\ExecutionEntity;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;
use BpmPlatform\Engine\Runtime\ActivityInstanceInterface;

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
