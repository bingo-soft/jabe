<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\ProcessInstanceModificationBuilderImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypes
};
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    ExecutionManager,
    PropertyChange
};
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Runtime\ProcessInstanceInterface;

abstract class AbstractDeleteProcessInstanceCmd
{
    protected $externallyTerminated;
    protected $deleteReason;
    protected $skipCustomListeners;
    protected $skipSubprocesses;
    protected $failIfNotExists = true;

    protected function checkDeleteProcessInstance(ExecutionEntity $execution, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkDeleteProcessInstance($execution);
        }
    }

    protected function deleteProcessInstance(
        CommandContext $commandContext,
        string $processInstanceId,
        string $deleteReason,
        bool $skipCustomListeners,
        bool $externallyTerminated,
        bool $skipIoMappings,
        bool $skipSubprocesses
    ): void {
        EnsureUtil::ensureNotNull("processInstanceId is null", "processInstanceId", $processInstanceId);

        // fetch process instance
        $executionManager = $commandContext->getExecutionManager();
        $execution = $executionManager->findExecutionById($processInstanceId);

        if (!$this->failIfNotExists && $execution === null) {
            return;
        }

        EnsureUtil::ensureNotNull(
            "No process instance found for id '" . $processInstanceId . "'",
            "processInstance",
            $execution
        );

        $this->checkDeleteProcessInstance($execution, $commandContext);

        // delete process instance
        $commandContext
            ->getExecutionManager()
            ->deleteProcessInstance($processInstanceId, $deleteReason, false, $skipCustomListeners, $externallyTerminated, $skipIoMappings, $skipSubprocesses);

        if ($skipSubprocesses) {
            $superProcesslist = $commandContext->getProcessEngineConfiguration()->getRuntimeService()->createProcessInstanceQuery()
                ->superProcessInstanceId($processInstanceId)->list();
            $this->triggerHistoryEvent($superProcesslist);
        }

        $superExecution = $execution->getSuperExecution();
        if ($superExecution !== null) {
            $commandContext->runWithoutAuthorization(function () use ($commandContext, $deleteReason, $externallyTerminated, $skipCustomListeners, $skipIoMappings, $superExecution) {
                $builder = (new ProcessInstanceModificationBuilderImpl($commandContext, $superExecution->getProcessInstanceId(), $deleteReason))
                ->cancellationSourceExternal($externallyTerminated)->cancelActivityInstance($superExecution->getActivityInstanceId());
                $builder->execute(false, $skipCustomListeners, $skipIoMappings);
                return null;
            });
        }

        // create user operation log
        $commandContext->getOperationLogManager()
            ->logProcessInstanceOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_DELETE,
                $processInstanceId,
                null,
                null,
                [PropertyChange::emptyChange()]
            );
    }

    public function triggerHistoryEvent(array $subProcesslist): void
    {
        $configuration = Context::getProcessEngineConfiguration();
        $historyLevel = $configuration->getHistoryLevel();

        foreach ($subProcesslist as $processInstance) {
            // TODO: This smells bad, as the rest of the history is done via the
            // ParseListener
            if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::processInstanceUpdate(), $processInstance)) {
                HistoryEventProcessor::processHistoryEvents(new class ($processInstance) extends HistoryEventCreator {
                    private $processInstance;

                    public function __construct(ProcessInstanceInterface $processInstance)
                    {
                        $this->processInstance = $processInstance;
                    }

                    public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                    {
                        return $producer->createProcessInstanceUpdateEvt($this->processInstance);
                    }
                });
            }
        }
    }
}
