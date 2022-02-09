<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    JobEntity
};
use BpmPlatform\Engine\Impl\Pvm\PvmActivityInterface;
use BpmPlatform\Engine\Impl\Pvm\Process\TransitionImpl;
use BpmPlatform\Engine\Impl\Pvm\Runtime\{
    AtomicOperation,
    LegacyBehavior
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class AsyncContinuationJobHandler implements JobHandlerInterface
{
    public const TYPE = "async-continuation";

    private $supportedOperations = [];

    public function __construct()
    {
        // async before activity
        $this->supportedOperations[AtomicOperation::transitionCreateScope()->getCanonicalName()] = AtomicOperation::transitionCreateScope();
        $this->supportedOperations[AtomicOperation::activityStartCreateScope()->getCanonicalName()] = AtomicOperation::activityStartCreateScope();
        // async before start event
        $this->supportedOperations[AtomicOperation::processStart()->getCanonicalName()] = AtomicOperation::processStart();

        // async after activity depending if an outgoing sequence flow exists
        $this->supportedOperations[AtomicOperation::transitionNotifyListenerTake()->getCanonicalName()] = AtomicOperation::transitionNotifyListenerTake();
        $this->supportedOperations[AtomicOperation::activityEnd()->getCanonicalName()] = AtomicOperation::activityEnd();
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function execute(AsyncContinuationConfiguration $configuration, ExecutionEntity $execution, CommandContext $commandContext, ?string $tenantId): void
    {
        LegacyBehavior::repairMultiInstanceAsyncJob($execution);

        $atomicOperation = $this->findMatchingAtomicOperation($configuration->getAtomicOperation());
        EnsureUtil::ensureNotNull("Cannot process job with configuration " . $configuration, "atomicOperation", $atomicOperation);

        // reset transition id.
        $transitionId = $configuration->getTransitionId();
        if ($transitionId != null) {
            $activity = $execution->getActivity();
            $transition = $activity->findOutgoingTransition($transitionId);
            $execution->setTransition($transition);
        }

        Context::getCommandInvocationContext()
            ->performOperation($atomicOperation, $execution);
    }

    public function findMatchingAtomicOperation(?string $operationName): ?AtomicOperation
    {
        if ($operationName == null) {
            // default operation for backwards compatibility
            return AtomicOperation::transitionCreateScope();
        } else {
            if (array_key_exists($operationName, $this->supportedOperations)) {
                return $this->supportedOperations[$operationName];
            }
        }
        return null;
    }

    protected function isSupported(AtomicOperation $atomicOperation): bool
    {
        return array_key_exists($atomicOperation->getCanonicalName(), $this->supportedOperations);
    }

    public function newConfiguration(string $canonicalString): AsyncContinuationConfiguration
    {
        $configParts = $this->tokenizeJobConfiguration($canonicalString);

        $configuration = new AsyncContinuationConfiguration();

        if (count($configuration) > 0) {
            $configuration->setAtomicOperation($configParts[0]);
        }
        if (count($configuration) > 1) {
            $configuration->setTransitionId($configParts[1]);
        }

        return $configuration;
    }

    /**
     * @return an array of length two with the following contents:
     * <ul><li>First element: pvm atomic operation name
     * <li>Second element: transition id (may be null)
     */
    protected function tokenizeJobConfiguration(?string $jobConfiguration): array
    {
        $configuration = [];

        if ($jobConfiguration != null) {
            $configParts = explode('$', $jobConfiguration);
            if (count($configuration) > 2) {
                throw new ProcessEngineException("Illegal async continuation job handler configuration: '" . $jobConfiguration . "': exprecting one part or two parts seperated by '$'.");
            }
            $configuration = $configParts;
        }

        return $configuration;
    }

    public function onDelete(AsyncContinuationConfiguration $configuration, JobEntity $jobEntity): void
    {
      // do nothing
    }
}
