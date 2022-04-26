<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    HistoricTaskInstanceEntity,
    PropertyChange
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class DeleteHistoricTaskInstanceCmd implements CommandInterface, \Serializable
{
    protected $taskId;

    public function __construct(string $taskId)
    {
        $this->taskId = $taskId;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("taskId", $this->taskId);

        $task = $commandContext->getHistoricTaskInstanceManager()->findHistoricTaskInstanceById($this->taskId);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkDeleteHistoricTaskInstance($task);
        }

        $this->writeUserOperationLog($commandContext, $task);

        $commandContext
            ->getHistoricTaskInstanceManager()
            ->deleteHistoricTaskInstanceById($this->taskId);

        return null;
    }

    public function writeUserOperationLog(CommandContext $commandContext, HistoricTaskInstanceEntity $historicTask): void
    {
        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("nrOfInstances", null, 1);
        $propertyChanges[] = new PropertyChange("async", null, false);

        $commandContext->getOperationLogManager()
            ->logTaskOperations(
                UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_HISTORY,
                $historicTask,
                $propertyChanges
            );
    }
}
