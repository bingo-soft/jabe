<?php

namespace Jabe\Impl\Cmd;

use Jabe\{
    ProcessEngineConfiguration,
    ProcessEngineException
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\History\Event\HistoricProcessInstanceEventEntity;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    CommentEntity,
    ExecutionEntity,
    TaskEntity
};
use Jabe\Impl\Util\{
    ClockUtil,
    EnsureUtil
};
use Jabe\Task\{
    CommentInterface,
    EventInterface
};

class AddCommentCmd implements CommandInterface, \Serializable
{
    protected $taskId;
    protected $processInstanceId;
    protected $message;

    public function __construct(?string $taskId, ?string $processInstanceId, ?string $message)
    {
        $this->taskId = $taskId;
        $this->processInstanceId = $processInstanceId;
        $this->message = $message;
    }

    public function serialize()
    {
        return json_encode([
            'taskId' => $this->taskId,
            'processInstanceId' => $this->processInstanceId,
            'message' => $this->message
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->taskId = $json->taskId;
        $this->processInstanceId = $json->processInstanceId;
        $this->message = $json->message;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        if ($this->processInstanceId === null && $this->taskId === null) {
            throw new ProcessEngineException("Process instance id and task id is null");
        }

        EnsureUtil::ensureNotNull("Message", "message", $this->message);

        $userId = $commandContext->getAuthenticatedUserId();
        $comment = new CommentEntity();
        $comment->setUserId($userId);
        $comment->setType(CommentEntity::TYPE_COMMENT);
        $comment->setTime(ClockUtil::getCurrentTime()->format('c'));
        $comment->setTaskId($this->taskId);
        $comment->setProcessInstanceId($this->processInstanceId);
        $comment->setAction(EventInterface::ACTION_ADD_COMMENT);

        $execution = $this->getExecution($commandContext);
        if ($execution !== null) {
            $comment->setRootProcessInstanceId($execution->getRootProcessInstanceId());
        }

        if ($this->isHistoryRemovalTimeStrategyStart()) {
            $this->provideRemovalTime($comment);
        }

        $eventMessage = str_replace('/\s+/', ' ', $this->message);
        if (strlen($eventMessage) > 163) {
            $eventMessage = substr($eventMessage, 0, 160) . "...";
        }
        $comment->setMessage($eventMessage);

        $comment->setFullMessage($this->message);

        $commandContext
            ->getCommentManager()
            ->insert($comment);

        $task = $this->getTask($commandContext);
        if ($task !== null) {
            $task->triggerUpdateEvent();
        }

        return $comment;
    }

    protected function getExecution(CommandContext $commandContext): ?ExecutionEntity
    {
        if ($this->taskId !== null) {
            $task = $this->getTask($commandContext);
            if ($task !== null) {
                return $task->getExecution();
            } else {
                return null;
            }
        } else {
            return $this->getProcessInstance($commandContext);
        }
    }

    protected function getProcessInstance(CommandContext $commandContext): ?ExecutionEntity
    {
        if ($this->processInstanceId !== null) {
            return $commandContext->getExecutionManager()->findExecutionById($this->processInstanceId);
        } else {
            return null;
        }
    }

    protected function getTask(CommandContext $commandContext): ?TaskEntity
    {
        if ($this->taskId !== null) {
            return $commandContext->getTaskManager()->findTaskById($this->taskId);
        } else {
            return null;
        }
    }

    protected function isHistoryRemovalTimeStrategyStart(): bool
    {
        return ProcessEngineConfiguration::HISTORY_REMOVAL_TIME_STRATEGY_START == $this->getHistoryRemovalTimeStrategy();
    }

    protected function getHistoryRemovalTimeStrategy(): ?string
    {
        return Context::getProcessEngineConfiguration()
            ->getHistoryRemovalTimeStrategy();
    }

    protected function getHistoricRootProcessInstance(?string $rootProcessInstanceId): HistoricProcessInstanceEventEntity
    {
        return Context::getCommandContext()
            ->getDbEntityManager()
            ->selectById(HistoricProcessInstanceEventEntity::class, $rootProcessInstanceId);
    }

    protected function provideRemovalTime(CommentEntity $comment): void
    {
        $rootProcessInstanceId = $comment->getRootProcessInstanceId();
        if ($rootProcessInstanceId !== null) {
            $historicRootProcessInstance = $this->getHistoricRootProcessInstance($rootProcessInstanceId);
            if ($historicRootProcessInstance !== null) {
                $removalTime = $historicRootProcessInstance->getRemovalTime();
                $comment->setRemovalTime($removalTime);
            }
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
