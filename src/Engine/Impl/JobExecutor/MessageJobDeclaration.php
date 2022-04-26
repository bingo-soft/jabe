<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Interceptor\AtomicOperationInvocation;
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    MessageEntity
};
use Jabe\Engine\Impl\Pvm\PvmActivityInterface;
use Jabe\Engine\Impl\Pvm\Runtime\AtomicOperationInterface;

class MessageJobDeclaration extends JobDeclaration
{
    public const ASYNC_BEFORE = "async-before";
    public const ASYNC_AFTER = "async-after";

    protected $operationIdentifier;

    public function __construct(array $operationsIdentifier)
    {
        parent::__construct(AsyncContinuationJobHandler::TYPE);
        $this->operationIdentifier = $operationsIdentifier;
    }

    protected function newJobInstance($context = null): MessageEntity
    {
        $message = new MessageEntity();
        $message->setExecution($context->getExecution());
        return $message;
    }

    public function isApplicableForOperation(AtomicOperationInterface $operation): bool
    {
        foreach ($this->operationIdentifier as $identifier) {
            if ($operation->getCanonicalName() == $identifier) {
                return true;
            }
        }
        return false;
    }

    protected function resolveExecution(AtomicOperationInvocation $context): ExecutionEntity
    {
        return $context->getExecution();
    }

    protected function resolveJobHandlerConfiguration(AtomicOperationInvocation $context): JobHandlerConfiguration
    {
        $configuration = new AsyncContinuationConfiguration();

        $configuration->setAtomicOperation($context->getOperation()->getCanonicalName());

        $execution = $context->getExecution();
        $activity = $execution->getActivity();
        if ($activity != null && $activity->isAsyncAfter()) {
            if ($execution->getTransition() != null) {
                // store id of selected transition in case this is async after.
                // id is not serialized with the execution -> we need to remember it as
                // job handler configuration.
                $configuration->setTransitionId($execution->getTransition()->getId());
            }
        }

        return $configuration;
    }
}
