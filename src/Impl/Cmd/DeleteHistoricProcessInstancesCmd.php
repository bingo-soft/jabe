<?php

namespace Jabe\Impl\Cmd;

use Jabe\BadUserRequestException;
use Jabe\History\{
    HistoricProcessInstanceInterface,
    UserOperationLogEntryInterface
};
use Jabe\Impl\HistoricProcessInstanceQueryImpl;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\PropertyChange;
use Jabe\Impl\Util\EnsureUtil;

class DeleteHistoricProcessInstancesCmd implements CommandInterface
{
    protected $processInstanceIds;
    protected $failIfNotExists;

    public function __construct(array $processInstanceIds, bool $failIfNotExists)
    {
        $this->processInstanceIds = $processInstanceIds;
        $this->failIfNotExists = $failIfNotExists;
    }

    public function __serialize(): array
    {
        return [
            'processInstanceIds' => $this->processInstanceIds,
            'failIfNotExists' => $this->failIfNotExists
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processInstanceIds = $data['processInstanceIds'];
        $this->failIfNotExists = $data['failIfNotExists'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "processInstanceIds", $this->processInstanceIds);
        EnsureUtil::ensureNotContainsNull("processInstanceId is null", "processInstanceIds", $this->processInstanceIds);

        // Check if process instance is still running
        $processInstanceIds = $this->processInstanceIds;
        $instances = $commandContext->runWithoutAuthorization(function () use ($processInstanceIds) {
            return (new HistoricProcessInstanceQueryImpl())->processInstanceIds($processInstanceIds);
        });

        if ($this->failIfNotExists) {
            if (count($processInstanceIds) == 1) {
                EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "historicProcessInstanceIds", $instances);
            } else {
                EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "historicProcessInstanceIds", $instances);
            }
        }

        $existingIds = [];

        foreach ($instances as $historicProcessInstance) {
            $existingIds[] = $historicProcessInstance->getId();

            foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                $checker->checkDeleteHistoricProcessInstance($historicProcessInstance);
            }

            EnsureUtil::ensureNotNull(BadUserRequestException::class, "instance.getEndTime()", $historicProcessInstance->getEndTime());
        }

        if ($this->executefailIfNotExists) {
            $nonExistingIds = array_diff($processInstanceIds, $existingIds);
            if (count($nonExistingIds) != 0) {
                throw new BadUserRequestException("No historic process instance found with id: "  . json_encode($nonExistingIds));
            }
        }

        if (count($existingIds) > 0) {
            $commandContext->getHistoricProcessInstanceManager()->deleteHistoricProcessInstanceByIds($existingIds);
        }
        $this->writeUserOperationLog($commandContext, count($existingIds));

        return null;
    }

    public function writeUserOperationLog(CommandContext $commandContext, int $numInstances): void
    {
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("nrOfInstances", null, $numInstances);
        $propertyChanges[] = new PropertyChange("async", null, false);
        $commandContext->getOperationLogManager()
        ->logProcessInstanceOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_HISTORY,
            null,
            null,
            null,
            $propertyChanges,
            null
        );
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
