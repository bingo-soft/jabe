<?php

namespace Jabe\Engine\Impl\History\Handler;

use Jabe\Engine\History\HistoricVariableInstanceInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\EntityManager\DbEntityManager;
use Jabe\Engine\Impl\History\Event\{
    //HistoricDecisionEvaluationEvent
    HistoricScopeInstanceEvent,
    HistoricVariableUpdateEventEntity,
    HistoryEvent,
    HistoryEventTypes
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ByteArrayEntity,
    HistoricVariableInstanceEntity
};
use Jabe\Engine\Repository\ResourceTypes;

class DbHistoryEventHandler implements HistoryEventHandlerInterface
{
    public function handleEvent(HistoryEvent $historyEvent): void
    {
        if ($historyEvent instanceof HistoricVariableUpdateEventEntity) {
            $this->insertHistoricVariableUpdateEntity($historyEvent);
        } else {
            $this->insertOrUpdate($historyEvent);
        }
        /* elseif (historyEvent instanceof HistoricDecisionEvaluationEvent) {
            insertHistoricDecisionEvaluationEvent((HistoricDecisionEvaluationEvent) historyEvent);
        }*/
    }

    public function handleEvents(array $historyEvents): void
    {
        foreach ($historyEvents as $historyEvent) {
            $this->handleEvent($historyEvent);
        }
    }

    /** general history event insert behavior */
    protected function insertOrUpdate(HistoryEvent $historyEvent): void
    {
        $dbEntityManager = $this->getDbEntityManager();

        if ($this->isInitialEvent($historyEvent)) {
            $dbEntityManager->insert($historyEvent);
        } else {
            if ($dbEntityManager->getCachedEntity(get_class($historyEvent), $historyEvent->getId()) === null) {
                if ($historyEvent instanceof HistoricScopeInstanceEvent) {
                    // if this is a scope, get start time from existing event in DB
                    $existingEvent = $dbEntityManager->selectById(get_class($historyEvent), $historyEvent->getId());
                    if ($existingEvent !== null) {
                        $historicScopeInstanceEvent = $historyEvent;
                        $historicScopeInstanceEvent->setStartTime($existingEvent->getStartTime());
                    }
                }
                if ($historyEvent->getId() === null) {
        //          dbSqlSession.insert(historyEvent);
                } else {
                    $dbEntityManager->merge($historyEvent);
                }
            }
        }
    }

    /** customized insert behavior for HistoricVariableUpdateEventEntity */
    protected function insertHistoricVariableUpdateEntity(HistoricVariableUpdateEventEntity $historyEvent): void
    {
        $dbEntityManager = $this->getDbEntityManager();
        // insert update only if history level = FULL
        if ($this->shouldWriteHistoricDetail($historyEvent)) {
            // insert byte array entity (if applicable)
            $byteValue = $historyEvent->getByteValue();
            if ($byteValue !== null) {
                $byteArrayEntity = new ByteArrayEntity($historyEvent->getVariableName(), $byteValue, ResourceTypes::history());
                $byteArrayEntity->setRootProcessInstanceId($historyEvent->getRootProcessInstanceId());
                $byteArrayEntity->setRemovalTime($historyEvent->getRemovalTime());

                Context::getCommandContext()
                ->getByteArrayManager()
                ->insertByteArray($byteArrayEntity);
                $historyEvent->setByteArrayId($byteArrayEntity->getId());
            }
            $dbEntityManager->insert($historyEvent);
        }

        // always insert/update HistoricProcessVariableInstance
        if ($historyEvent->isEventOfType(HistoryEventTypes::variableInstanceCreate())) {
            $persistentObject = new HistoricVariableInstanceEntity($historyEvent);
            $dbEntityManager->insert($persistentObject);
        } elseif (
            $historyEvent->isEventOfType(HistoryEventTypes::variableInstanceUpdate())
            || $historyEvent->isEventOfType(HistoryEventTypes::variableInstanceMigrate())
        ) {
            $historicVariableInstanceEntity = $dbEntityManager->selectById(HistoricVariableInstanceEntity::class, $historyEvent->getVariableInstanceId());
            if ($historicVariableInstanceEntity !== null) {
                $historicVariableInstanceEntity->updateFromEvent($historyEvent);
                $historicVariableInstanceEntity->setState(HistoricVariableInstance::stateCreated());
            } else {
                // #CAM-1344 / #SUPPORT-688
                // this is a FIX for process instances which were started in camunda fox 6.1 and migrated to Camunda Platform 7.0.
                // in fox 6.1 the HistoricVariable instances were flushed to the DB when the process instance completed.
                // Since fox 6.2 we populate the HistoricVariable table as we go.
                $persistentObject = new HistoricVariableInstanceEntity($historyEvent);
                $dbEntityManager->insert($persistentObject);
            }
        } elseif ($historyEvent->isEventOfType(HistoryEventTypes::variableInstanceDelete())) {
            $historicVariableInstanceEntity = $dbEntityManager->selectById(HistoricVariableInstanceEntity::class, $historyEvent->getVariableInstanceId());
            if ($historicVariableInstanceEntity !== null) {
                $historicVariableInstanceEntity->setState(HistoricVariableInstance::stateDeleted());
            }
        }
    }

    protected function shouldWriteHistoricDetail(HistoricVariableUpdateEventEntity $historyEvent): bool
    {
        return Context::getProcessEngineConfiguration()->getHistoryLevel()
            ->isHistoryEventProduced(HistoryEventTypes::variableInstanceUpdateDetail(), $historyEvent)
            && !$historyEvent->isEventOfType(HistoryEventTypes::variableInstanceMigrate());
    }

    /*protected function insertHistoricDecisionEvaluationEvent(HistoricDecisionEvaluationEvent $event): void
    {
        Context::getCommandContext()
        ->getHistoricDecisionInstanceManager()
        ->insertHistoricDecisionInstances($event);
    }*/

    protected function isInitialEvent(HistoryEvent $historyEvent): bool
    {
        return $historyEvent->getEventType() === null
            || $historyEvent->isEventOfType(HistoryEventTypes::activityInstanceStart())
            || $historyEvent->isEventOfType(HistoryEventTypes::processInstanceStart())
            || $historyEvent->isEventOfType(HistoryEventTypes::taskInstanceCreate())
            || $historyEvent->isEventOfType(HistoryEventTypes::formPropertyUpdate())
            || $historyEvent->isEventOfType(HistoryEventTypes::incidentCreate())
            //|| $historyEvent->isEventOfType(HistoryEventTypes.CASE_INSTANCE_CREATE)
            //|| $historyEvent->isEventOfType(HistoryEventTypes.DMN_DECISION_EVALUATE)
            || $historyEvent->isEventOfType(HistoryEventTypes::batchStart())
            || $historyEvent->isEventOfType(HistoryEventTypes::identityLinkAdd())
            || $historyEvent->isEventOfType(HistoryEventTypes::identityLinkDelete())
            ;
    }

    protected function getDbEntityManager(): DbEntityManager
    {
        return Context::getCommandContext()->getDbEntityManager();
    }
}
