<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Util\EnsureUtil;

class TransitionInstanceCancellationCmd extends AbstractInstanceCancellationCmd
{
    protected $transitionInstanceId;

    public function __construct(?string $processInstanceId, ?string $transitionInstanceId)
    {
        parent::__construct($processInstanceId);
        $this->transitionInstanceId = $transitionInstanceId;
    }

    public function getTransitionInstanceId(): ?string
    {
        return $this->transitionInstanceId;
    }

    protected function determineSourceInstanceExecution(CommandContext $commandContext): ExecutionEntity
    {
        $processInstanceId = $this->processInstanceId;
        $instance = $commandContext->runWithoutAuthorization(function () use ($commandContext, $processInstanceId) {
            $cmd = new GetActivityInstanceCmd($processInstanceId);
            return $cmd->execute($commandContext);
        });
        $instanceToCancel = $this->findTransitionInstance($instance, $this->transitionInstanceId);
        EnsureUtil::ensureNotNull(
            $this->describeFailure("Transition instance '" . $this->transitionInstanceId . "' does not exist"),
            "transitionInstance",
            $instanceToCancel
        );

        $transitionExecution = $commandContext->getExecutionManager()->findExecutionById($instanceToCancel->getExecutionId());

        return $transitionExecution;
    }

    protected function describe(): ?string
    {
        return "Cancel transition instance '" . $this->transitionInstanceId . "'";
    }
}
