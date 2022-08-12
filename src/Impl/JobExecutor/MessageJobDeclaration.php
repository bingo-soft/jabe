<?php

namespace Jabe\Impl\JobExecutor;

use Jabe\Impl\Interceptor\AtomicOperationInvocation;
use Jabe\Impl\JobExecutor\JobHandlerConfigurationInterface;
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity,
    MessageEntity
};
use Jabe\Impl\Pvm\Runtime\AtomicOperationInterface;

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

    protected function newJobInstance($context = null): JobEntity
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

    protected function resolveExecution(/*AtomicOperationInvocation*/$context): ?ExecutionEntity
    {
        return $context->getExecution();
    }

    protected function resolveJobHandlerConfiguration(/*AtomicOperationInvocation*/$context): JobHandlerConfigurationInterface
    {
        $configuration = new AsyncContinuationConfiguration();

        $configuration->setAtomicOperation($context->getOperation()->getCanonicalName());

        $execution = $context->getExecution();
        $activity = $execution->getActivity();
        if ($activity !== null && $activity->isAsyncAfter()) {
            if ($execution->getTransition() !== null) {
                // store id of selected transition in case this is async after.
                // id is not serialized with the execution -> we need to remember it as
                // job handler configuration.
                $configuration->setTransitionId($execution->getTransition()->getId());
            }
        }

        return $configuration;
    }
}
