<?php

namespace Jabe\Impl\Cmd;

use Jabe\BadUserRequestException;
use Jabe\Exception\NotValidException;
use Jabe\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypes
};
use Jabe\Impl\History\Producer\HistoryEventProducerInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\IncidentEntity;
use Jabe\Impl\Util\EnsureUtil;

class SetAnnotationForIncidentCmd implements CommandInterface
{
    protected $incidentId;
    protected $annotation;

    public function __construct(string $incidentId, string $annotation)
    {
        $this->incidentId = $incidentId;
        $this->annotation = $annotation;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "incident id", $this->incidentId);

        $incident = $commandContext->getIncidentManager()->findIncidentById($this->incidentId);
        EnsureUtil::ensureNotNull(BadUserRequestException::class, "incident", $incident);

        if ($incident->getExecutionId() !== null) {
            $execution = $commandContext->getExecutionManager()->findExecutionById($incident->getExecutionId());
            if ($execution !== null) {
                // check rights for updating an execution-related incident
                foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
                    $checker->checkUpdateProcessInstance($execution);
                }
            }
        }

        $incident->setAnnotation($this->annotation);

        $this->triggerHistoryEvent($commandContext, $incident);

        if ($this->annotation === null) {
            $commandContext->getOperationLogManager()
                ->logClearIncidentAnnotationOperation($this->incidentId);
        } else {
            $commandContext->getOperationLogManager()
                ->logSetIncidentAnnotationOperation($this->incidentId);
        }
        return null;
    }

    protected function triggerHistoryEvent(CommandContext $commandContext, IncidentEntity $incident): void
    {
        $historyLevel = $commandContext->getProcessEngineConfiguration()->getHistoryLevel();
        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::incidentUpdate(), $incident)) {
            $annotation = $this->annotation;
            HistoryEventProcessor::processHistoryEvents(new class ($incident, $annotation) extends HistoryEventCreator {
                private $incident;
                private $annotation;

                public function __construct($incident, $annotation)
                {
                    $this->incident = $incident;
                    $this->annotation = $annotation;
                }

                public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                {
                    $incidentUpdateEvt = $producer->createHistoricIncidentUpdateEvt($this->incident);
                    $incidentUpdateEvt->setAnnotation($this->annotation);
                    return $incidentUpdateEvt;
                }
            });
        }
    }
}
