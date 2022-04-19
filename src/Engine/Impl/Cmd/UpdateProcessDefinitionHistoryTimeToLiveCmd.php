<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ProcessDefinitionEntity,
    PropertyChange
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class UpdateProcessDefinitionHistoryTimeToLiveCmd implements CommandInterface, \Serializable
{
    protected $processDefinitionId;
    protected $historyTimeToLive;

    public function __construct(string $processDefinitionId, ?int $historyTimeToLive)
    {
        $this->processDefinitionId = $processDefinitionId;
        $this->historyTimeToLive = $historyTimeToLive;
    }

    public function serialize()
    {
        return json_encode([
            'processDefinitionId' => $this->processDefinitionId,
            'historyTimeToLive' => $this->historyTimeToLive
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processDefinitionId = $json->processDefinitionId;
        $this->historyTimeToLive = $json->historyTimeToLive;
    }

    public function execute(CommandContext $commandContext)
    {
        $this->checkAuthorization($commandContext);
        EnsureUtil::ensureNotNull(BadUserRequestException::class, "processDefinitionId", $this->processDefinitionId);
        if ($this->historyTimeToLive != null) {
            EnsureUtil::ensureGreaterThanOrEqual("historyTimeToLive", $this->historyTimeToLive, 0);
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
}
