<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\EntityManager\DbEntityManager;
use Jabe\Engine\Impl\History\Event\HistoricProcessInstanceEventEntity;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    AttachmentEntity,
    ByteArrayEntity,
    ExecutionEntity,
    PropertyChange,
    TaskEntity
};
use Jabe\Engine\Impl\Util\{
    ClockUtil,
    EnsureUtil,
    IoUtil
};
use Jabe\Engine\ProcessEngineConfiguration;
use Jabe\Engine\Repository\ResourceTypes;
use Jabe\Engine\Task\AttachmentInterface;

class CreateAttachmentCmd implements CommandInterface
{
    protected $taskId;
    protected $attachmentType;
    protected $processInstanceId;
    protected $attachmentName;
    protected $attachmentDescription;
    protected $content;
    protected $url;
    private $task;
    protected $processInstance;

    public function __construct(string $attachmentType, string $taskId, string $processInstanceId, string $attachmentName, string $attachmentDescription, $content = null, string $url = null)
    {
        $this->attachmentType = $attachmentType;
        $this->taskId = $taskId;
        $this->processInstanceId = $processInstanceId;
        $this->attachmentName = $attachmentName;
        $this->attachmentDescription = $attachmentDescription;
        $this->content = $content;
        $this->url = $url;
    }

    public function execute(CommandContext $commandContext)
    {
        if ($this->taskId !== null) {
            $this->task = $commandContext
                ->getTaskManager()
                ->findTaskById($this->taskId);
        } else {
            EnsureUtil::ensureNotNull("taskId or processInstanceId has to be provided", $this->processInstanceId);
            $executionsByProcessInstanceId = $commandContext->getExecutionManager()->findExecutionsByProcessInstanceId($this->processInstanceId);
            $this->processInstance = $executionsByProcessInstanceId[0];
        }

        $attachment = new AttachmentEntity();
        $attachment->setName($this->attachmentName);
        $attachment->setDescription($this->attachmentDescription);
        $attachment->setType($this->attachmentType);
        $attachment->setTaskId($this->taskId);
        $attachment->setProcessInstanceId($this->processInstanceId);
        $attachment->setUrl($this->url);
        $attachment->setCreateTime(ClockUtil::getCurrentTime()->format('c'));

        if ($this->task !== null) {
            $execution = $this->task->getExecution();
            if ($execution !== null) {
                $attachment->setRootProcessInstanceId($execution->getRootProcessInstanceId());
            }
        } elseif ($this->processInstance !== null) {
            $attachment->setRootProcessInstanceId($this->processInstance->getRootProcessInstanceId());
        }

        if ($this->isHistoryRemovalTimeStrategyStart()) {
            $this->provideRemovalTime($attachment);
        }

        $dbEntityManger = $commandContext->getDbEntityManager();
        $dbEntityManger->insert($attachment);

        if ($content !== null) {
            $bytes = IoUtil::readInputStream($content, $attachmentName);
            $byteArray = new ByteArrayEntity($bytes, ResourceTypes::history());

            $byteArray->setRootProcessInstanceId($attachment->getRootProcessInstanceId());
            $byteArray->setRemovalTime($attachment->getRemovalTime());

            $commandContext->getByteArrayManager()->insertByteArray($byteArray);
            $attachment->setContentId($byteArray->getId());
        }

        $propertyChange = new PropertyChange("name", null, $this->attachmentName);

        if ($this->task !== null) {
            $commandContext->getOperationLogManager()
                ->logAttachmentOperation(UserOperationLogEntryInterface::OPERATION_TYPE_ADD_ATTACHMENT, $this->task, $propertyChange);
            $this->task->triggerUpdateEvent();
        } elseif ($this->processInstance !== null) {
            $commandContext->getOperationLogManager()
                ->logAttachmentOperation(UserOperationLogEntryInterface::OPERATION_TYPE_ADD_ATTACHMENT, $this->processInstance, $propertyChange);
        }

        return $attachment;
    }

    protected function isHistoryRemovalTimeStrategyStart(): bool
    {
        return ProcessEngineConfiguration::HISTORY_REMOVAL_TIME_STRATEGY_START == $this->getHistoryRemovalTimeStrategy();
    }

    protected function getHistoryRemovalTimeStrategy(): string
    {
        return Context::getProcessEngineConfiguration()
            ->getHistoryRemovalTimeStrategy();
    }

    protected function getHistoricRootProcessInstance(string $rootProcessInstanceId): HistoricProcessInstanceEventEntity
    {
        return Context::getCommandContext()->getDbEntityManager()
            ->selectById(HistoricProcessInstanceEventEntity::class, $rootProcessInstanceId);
    }

    protected function provideRemovalTime(AttachmentEntity $attachment): void
    {
        $rootProcessInstanceId = $attachment->getRootProcessInstanceId();
        if ($rootProcessInstanceId !== null) {
            $historicRootProcessInstance = $this->getHistoricRootProcessInstance($rootProcessInstanceId);
            if ($historicRootProcessInstance !== null) {
                $removalTime = $historicRootProcessInstance->getRemovalTime();
                $attachment->setRemovalTime($removalTime);
            }
        }
    }
}
