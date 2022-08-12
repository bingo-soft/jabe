<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\JobExecutor\JobHandlerConfigurationInterface;
use Jabe\Impl\Persistence\Entity\{
    JobDefinitionEntity,
    JobDefinitionManager,
    ProcessDefinitionEntity,
    SuspensionState,
    TimerEntity
};

abstract class AbstractSetStateCmd implements CommandInterface
{
    protected const SUSPENSION_STATE_PROPERTY = "suspensionState";

    protected $includeSubResources;
    protected $isLogUserOperationDisabled;
    protected $executionDate;

    public function __construct(bool $includeSubResources, string $executionDate)
    {
        $this->includeSubResources = $includeSubResources;
        $this->executionDate = $executionDate;
    }

    public function execute(CommandContext $commandContext)
    {
        $this->checkParameters($commandContext);
        $this->checkAuthorization($commandContext);

        if ($this->executionDate === null) {
            $this->updateSuspensionState($commandContext, $this->getNewSuspensionState());

            if ($this->isIncludeSubResources()) {
                $cmd = $this->getNextCommand();
                if ($cmd !== null) {
                    $cmd->disableLogUserOperation();
                    // avoids unnecessary authorization checks
                    // pre-requirement: the necessary authorization check
                    // for included resources should be done before this
                    // call.
                    $commandContext->runWithoutAuthorization(function () use ($cmd, $commandContext) {
                        $cmd->execute($commandContext);
                    });
                }
            }

            $this->triggerHistoryEvent($commandContext);
        } else {
            $this->scheduleSuspensionStateUpdate($commandContext);
        }

        if (!$this->isLogUserOperationDisabled()) {
            $this->logUserOperation($commandContext);
        }

        return null;
    }

    protected function triggerHistoryEvent(CommandContext $commandContext): void
    {
    }

    public function disableLogUserOperation(): void
    {
        $this->isLogUserOperationDisabled = true;
    }

    protected function isLogUserOperationDisabled(): bool
    {
        return $this->isLogUserOperationDisabled;
    }

    protected function isIncludeSubResources(): bool
    {
        return $this->includeSubResources;
    }

    protected function scheduleSuspensionStateUpdate(CommandContext $commandContext): void
    {
        $timer = new TimerEntity();

        $jobHandlerConfiguration = $this->getJobHandlerConfiguration();

        $timer->setDuedate($this->executionDate);
        $timer->setJobHandlerType($this->getDelayedExecutionJobHandlerType());
        $timer->setJobHandlerConfigurationRaw($jobHandlerConfiguration->toCanonicalString());
        $timer->setDeploymentId($this->getDeploymentId($commandContext));

        $commandContext->getJobManager()->schedule($timer);
    }

    protected function getDelayedExecutionJobHandlerType(): ?string
    {
        return null;
    }

    protected function getJobHandlerConfiguration(): ?JobHandlerConfigurationInterface
    {
        return null;
    }

    protected function getNextCommand()
    {
        return null;
    }

    /**
     * @return string the id of the associated deployment, only necessary if the command
     *         can potentially be executed in a scheduled way (i.e. if an
     *         {@link #executionDate} can be set) so the job executor responsible
     *         for that deployment can execute the resulting job
     */
    protected function getDeploymentId(CommandContext $commandContext): ?string
    {
        return null;
    }

    abstract protected function checkAuthorization(CommandContext $commandContext): void;

    abstract protected function checkParameters(CommandContext $commandContext): void;

    abstract protected function updateSuspensionState(CommandContext $commandContext, SuspensionState $suspensionState): void;

    abstract protected function logUserOperation(CommandContext $commandContext): void;

    abstract protected function getLogEntryOperation(): string;

    abstract protected function getNewSuspensionState(): SuspensionState;

    protected function getDeploymentIdByProcessDefinition(CommandContext $commandContext, string $processDefinitionId): ?string
    {
        $definition = $commandContext->getProcessDefinitionManager()->getCachedResourceDefinitionEntity($this->processDefinitionId);
        if ($definition === null) {
            $definition = $commandContext->getProcessDefinitionManager()->findLatestDefinitionById($processDefinitionId);
        }
        if ($definition !== null) {
            return $definition->getDeploymentId();
        }
        return null;
    }

    protected function getDeploymentIdByProcessDefinitionKey(
        CommandContext $commandContext,
        string $processDefinitionKey,
        bool $tenantIdSet,
        string $tenantId
    ): ?string {
        $definition = null;
        if ($tenantIdSet) {
            $definition = $commandContext->getProcessDefinitionManager()->findLatestProcessDefinitionByKeyAndTenantId($processDefinitionKey, $tenantId);
        } else {
            // randomly use a latest process definition's deployment id from one of the tenants
            $definitions = $commandContext->getProcessDefinitionManager()->findLatestProcessDefinitionsByKey($processDefinitionKey);
            $definition = empty($definitions) ? null : $definitions[0];
        }
        if ($definition !== null) {
            return $definition->getDeploymentId();
        }
        return null;
    }

    protected function getDeploymentIdByJobDefinition(CommandContext $commandContext, string $jobDefinitionId): ?string
    {
        $jobDefinitionManager = $commandContext->getJobDefinitionManager();
        $jobDefinition = $jobDefinitionManager->findById($jobDefinitionId);
        if ($jobDefinition !== null) {
            if ($jobDefinition->getProcessDefinitionId() !== null) {
                return $this->getDeploymentIdByProcessDefinition($commandContext, $jobDefinition->getProcessDefinitionId());
            }
        }
        return null;
    }
}
