<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\{
    ExternalTaskQueryImpl,
    HistoricProcessInstanceQueryImpl,
    ProcessInstanceQueryImpl
};
use BpmPlatform\Engine\Impl\Batch\BatchElementConfiguration;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\PropertyChange;
use BpmPlatform\Engine\Impl\Util\{
    CollectionUtil,
    EnsureUtil
};

abstract class AbstractSetExternalTaskRetriesCmd implements CommandInterface
{
    protected $builder;

    public function __construct(UpdateExternalTaskRetriesBuilderImpl $builder)
    {
        $this->builder = $builder;
    }

    protected function collectProcessInstanceIds(): array
    {
        $collectedProcessInstanceIds = [];

        $processInstanceIds = $this->builder->getProcessInstanceIds();
        if (!empty($processInstanceIds)) {
            $collectedProcessInstanceIds = $processInstanceIds;
        }

        $processInstanceQuery = $builder->getProcessInstanceQuery();
        if ($processInstanceQuery != null) {
            $collectedProcessInstanceIds = array_merge($collectedProcessInstanceIds, $processInstanceQuery->listIds());
        }

        $historicProcessInstanceQuery = $builder->getHistoricProcessInstanceQuery();
        if ($historicProcessInstanceQuery != null) {
            $collectedProcessInstanceIds = array_merge($collectedProcessInstanceIds, $historicProcessInstanceQuery->istIds());
        }

        return $collectedProcessInstanceIds;
    }

    protected function collectExternalTaskIds(CommandContext $commandContext): BatchElementConfiguration
    {
        $elementConfiguration = new BatchElementConfiguration();

        $externalTaskIds = $builder->getExternalTaskIds();
        if (!empty($externalTaskIds)) {
            EnsureUtil::ensureNotContainsNull("External task id cannot be null", "externalTaskIds", $externalTaskIds);
            $taskQuery = new ExternalTaskQueryImpl();
            $taskQuery->externalTaskIdIn($externalTaskIds);
            $elementConfiguration->addDeploymentMappings(
                $commandContext->runWithoutAuthorization(function () use ($taskQuery) {
                    return $taskQuery->listDeploymentIdMappings();
                }),
                $externalTaskIds
            );
        }

        $externalTaskQuery = $builder->getExternalTaskQuery();
        if ($externalTaskQuery != null) {
            $elementConfiguration->addDeploymentMappings($externalTaskQuery->listDeploymentIdMappings());
        }

        $collectedProcessInstanceIds = $this->collectProcessInstanceIds();
        if (!empty($collectedProcessInstanceIds)) {
            $query = new ExternalTaskQueryImpl();
            $query->processInstanceIdIn($collectedProcessInstanceIds);
            $elementConfiguration->addDeploymentMappings($commandContext->runWithoutAuthorization(function () use ($query) {
                return $query->listDeploymentIdMappings();
            }));
        }

        return $elementConfiguration;
    }

    public function writeUserOperationLog(
        CommandContext $commandContext,
        int $numInstances,
        bool $async
    ): void {
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("nrOfInstances", null, $numInstances);
        $propertyChanges[] = new PropertyChange("async", null, $async);
        $propertyChanges[] = new PropertyChange("retries", null, $builder->getRetries());

        $commandContext->getOperationLogManager()->logExternalTaskOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_SET_EXTERNAL_TASK_RETRIES,
            null,
            $propertyChanges
        );
    }

    public function writeUserOperationLogAsync(CommandContext $commandContext, int $numInstances): void
    {
        $this->writeUserOperationLog($commandContext, $numInstances, true);
    }
}
