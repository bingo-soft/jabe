<?php

namespace Jabe\Impl\Cmd;

use Jabe\BadUserRequestException;
use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ProcessDefinitionEntity,
    PropertyChange
};
use Jabe\Impl\Util\EnsureUtil;

class UpdateProcessDefinitionHistoryTimeToLiveCmd implements CommandInterface
{
    protected $processDefinitionId;
    protected $historyTimeToLive;

    public function __construct(?string $processDefinitionId, ?int $historyTimeToLive)
    {
        $this->processDefinitionId = $processDefinitionId;
        $this->historyTimeToLive = $historyTimeToLive;
    }

    public function __serialize(): array
    {
        return [
            'processDefinitionId' => $this->processDefinitionId,
            'historyTimeToLive' => $this->historyTimeToLive
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->processDefinitionId = $data['processDefinitionId'];
        $this->historyTimeToLive = $data['historyTimeToLive'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $this->checkAuthorization($commandContext);
        EnsureUtil::ensureNotNull(BadUserRequestException::class, "processDefinitionId", $this->processDefinitionId);
        if ($this->historyTimeToLive !== null) {
            EnsureUtil::ensureGreaterThanOrEqual("History time to live cannot be negative", "historyTimeToLive", $this->historyTimeToLive, 0);
        }

        $processDefinitionEntity = $commandContext->getProcessDefinitionManager()->findLatestProcessDefinitionById($this->processDefinitionId);
        $this->logUserOperation($commandContext, $processDefinitionEntity);
        $processDefinitionEntity->setHistoryTimeToLive($this->historyTimeToLive);

        return null;
    }

    protected function checkAuthorization(CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessDefinitionById($this->processDefinitionId);
        }
    }

    protected function logUserOperation(CommandContext $commandContext, ProcessDefinitionEntity $processDefinitionEntity): void
    {
        $propertyChange = new PropertyChange("historyTimeToLive", $processDefinitionEntity->getHistoryTimeToLive(), $this->historyTimeToLive);
        $commandContext->getOperationLogManager()
            ->logProcessDefinitionOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_UPDATE_HISTORY_TIME_TO_LIVE,
                $this->processDefinitionId,
                $processDefinitionEntity->getKey(),
                $propertyChange
            );
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
