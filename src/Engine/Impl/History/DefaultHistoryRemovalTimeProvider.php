<?php

namespace BpmPlatform\Engine\Impl\History;

use BpmPlatform\Engine\Impl\Batch\History\HistoricBatchEntity;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoricProcessInstanceEventEntity
};
use BpmPlatform\Engine\Repository\{
    ProcessDefinitionInterface
};

class DefaultHistoryRemovalTimeProvider implements HistoryRemovalTimeProvider
{
    public function calculateRemovalTime($instance, $definition = null): ?string
    {
        if ($instance instanceof HistoricProcessInstanceEventEntity) {
            $historyTimeToLive = $definition->getHistoryTimeToLive();

            if ($historyTimeToLive != null) {
                if ($this->isProcessInstanceRunning($instance)) {
                    $startTime = $instance->getStartTime();
                    return self::determineRemovalTime($startTime, $historyTimeToLive);
                } elseif ($this->isProcessInstanceEnded($instance)) {
                    $endTime = $instance->getEndTime();
                    return self::determineRemovalTime($endTime, $historyTimeToLive);
                }
            }
        } elseif ($instance instanceof HistoricBatchEntity) {
            $batchOperation = $instance->getType();
            if ($batchOperation != null) {
                $historyTimeToLive = $this->getTTLByBatchOperation($batchOperation);
                if ($historyTimeToLive != null) {
                    if ($this->isBatchRunning($instance)) {
                        $startTime = $instance->getStartTime();
                        return self::determineRemovalTime($startTime, $historyTimeToLive);
                    } elseif ($this->isBatchEnded($instance)) {
                        $endTime = $instance->getEndTime();
                        return self::determineRemovalTime($endTime, $historyTimeToLive);
                    }
                }
            }
        }

        return null;
    }

    /*public Date calculateRemovalTime(HistoricDecisionInstanceEntity historicRootDecisionInstance, DecisionDefinition decisionDefinition) {

      Integer historyTimeToLive = decisionDefinition.getHistoryTimeToLive();

      if (historyTimeToLive != null) {
        Date evaluationTime = historicRootDecisionInstance.getEvaluationTime();
        return determineRemovalTime(evaluationTime, historyTimeToLive);
      }

      return null;
    }*/

    protected function isBatchRunning(HistoricBatchEntity $historicBatch): bool
    {
        return $historicBatch->getEndTime() == null;
    }

    protected function isBatchEnded(HistoricBatchEntity $historicBatch): bool
    {
        return $historicBatch->getEndTime() != null;
    }

    protected function getTTLByBatchOperation(string $batchOperation): int
    {
        return Context::getCommandContext()
            ->getProcessEngineConfiguration()
            ->getParsedBatchOperationsForHistoryCleanup()
            ->get($batchOperation);
    }

    protected function isProcessInstanceRunning(HistoricProcessInstanceEventEntity $historicProcessInstance): bool
    {
        return $historicProcessInstance->getEndTime() == null;
    }

    protected function isProcessInstanceEnded(HistoricProcessInstanceEventEntity $historicProcessInstance): bool
    {
        return $historicProcessInstance->getEndTime() != null;
    }

    public static function determineRemovalTime(string $initTime, int $timeToLive): string
    {
        return intval($initTime) + $timeToLive;
    }
}