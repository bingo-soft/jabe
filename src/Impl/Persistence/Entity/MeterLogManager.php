<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\{
    Direction,
    QueryOrderingProperty,
    QueryPropertyImpl
};
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Impl\Metrics\{
    Meter,
    MetricsQueryImpl
};
use Jabe\Impl\Persistence\AbstractManager;
use Jabe\Impl\Util\ClockUtil;

class MeterLogManager extends AbstractManager
{
    public const SELECT_METER_INTERVAL = "selectMeterLogAggregatedByTimeInterval";
    public const SELECT_METER_SUM = "selectMeterLogSum";
    public const DELETE_ALL_METER = "deleteAllMeterLogEntries";
    public const DELETE_ALL_METER_BY_TIMESTAMP_AND_REPORTER = "deleteMeterLogEntriesByTimestampAndReporter";

    public const SELECT_UNIQUE_TASK_WORKER = "selectUniqueTaskWorkerCount";
    public const SELECT_TASK_METER_FOR_CLEANUP = "selectTaskMetricIdsForCleanup";
    public const DELETE_TASK_METER_BY_TIMESTAMP = "deleteTaskMeterLogEntriesByTimestamp";
    public const DELETE_TASK_METER_BY_REMOVAL_TIME = "deleteTaskMetricsByRemovalTime";
    public const DELETE_TASK_METER_BY_IDS = "deleteTaskMeterLogEntriesByIds";

    public function insert(MeterLogEntity $meterLogEntity): void
    {
        $this->getDbEntityManager()
        ->insert($meterLogEntity);
    }

    public function executeSelectSum(MetricsQueryImpl $query): int
    {
        $result = $this->getDbEntityManager()->selectOne(self::SELECT_METER_SUM, $query);
        $result = $result !== null ? $result : 0;

        if ($this->shouldAddCurrentUnloggedCount($query)) {
            // add current unlogged count
            $meter = Context::getProcessEngineConfiguration()
            ->getMetricsRegistry()
            ->getDbMeterByName($query->getName());
            if ($meter !== null) {
                $result += $meter->get();
            }
        }

        return $result;
    }

    public function executeSelectInterval(MetricsQueryImpl $query): array
    {
        $intervalResult = $this->getDbEntityManager()->selectList(self::SELECT_METER_INTERVAL, $query);
        $intervalResult = $intervalResult !== null ? $intervalResult : [];

        $reporterId = Context::getProcessEngineConfiguration()->getDbMetricsReporter()->getMetricsCollectionTask()->getReporter();
        if (!empty($intervalResult) && $this->isEndTimeAfterLastReportInterval($query) && $reporterId !== null) {
            $metrics = Context::getProcessEngineConfiguration()->getMetricsRegistry()->getDbMeters();
            $queryName = $query->getName();
            //we have to add all unlogged metrics to last interval
            if ($queryName !== null) {
                $intervalEntity = $intervalResult[0];
                $entityValue = $intervalEntity->getValue();
                if (array_key_exists($queryName, $metrics)) {
                    $entityValue += $metrics[$queryName]->get();
                }
                $intervalEntity->setValue($entityValue);
            } else {
                $metricNames = array_keys($metrics);
                $lastIntervalTimestamp = $intervalResult[0]->getTimestamp();
                foreach ($metricNames as $metricName) {
                    $entity = new MetricIntervalEntity($lastIntervalTimestamp, $metricName, $reporterId);
                    foreach ($intervalResult as $idx => $test) {
                        if ($test == $entity) {
                            $intervalValue = $intervalResult[$idx];
                            $intervalValue->setValue($intervalValue->getValue() + $metrics[$metricName]->get());
                        }
                    }
                }
            }
        }
        return $intervalResult;
    }

    protected function isEndTimeAfterLastReportInterval(MetricsQueryImpl $query): bool
    {
        $reportingIntervalInSeconds = Context::getProcessEngineConfiguration()
            ->getDbMetricsReporter()
            ->getReportingIntervalInSeconds();

        return ($query->getEndDate() === null
            || $query->getEndDateMilliseconds() >= ClockUtil::getCurrentTime()->getTimestamp() * 1000 - (1000 * $reportingIntervalInSeconds));
    }

    protected function shouldAddCurrentUnloggedCount(MetricsQueryImpl $query): bool
    {
        return $query->getName() !== null
            && $this->isEndTimeAfterLastReportInterval($query);
    }

    public function deleteAll(): void
    {
        $this->getDbEntityManager()->delete(MeterLogEntity::class, self::DELETE_ALL_METER, null);
    }

    public function deleteByTimestampAndReporter(?string $timestamp, string $reporter): void
    {
        $parameters = [];
        if ($timestamp !== null) {
            $ut = (new \DateTime($timestamp))->getTimestamp();
            $parameters["milliseconds"] = $ut * 1000;
        }
        $parameters["reporter"] = $reporter;
        $this->getDbEntityManager()->delete(MeterLogEntity::class, self::DELETE_ALL_METER_BY_TIMESTAMP_AND_REPORTER, $parameters);
    }

    // TASK METER LOG
    public function findUniqueTaskWorkerCount(string $startTime, string $endTime): int
    {
        $parameters = [];
        $parameters["startTime"] = $startTime;
        $parameters["endTime"] = $endTime;

        return $this->getDbEntityManager()->selectOne(self::SELECT_UNIQUE_TASK_WORKER, $parameters);
    }

    public function deleteTaskMetricsByTimestamp(string $timestamp): void
    {
        $parameters = ["timestamp" => $timestamp];
        $this->getDbEntityManager()->delete(TaskMeterLogEntity::class, self::DELETE_TASK_METER_BY_TIMESTAMP, $parameters);
    }

    public function deleteTaskMetricsById(array $taskMetricIds): void
    {
        $this->getDbEntityManager()->deletePreserveOrder(TaskMeterLogEntity::class, self::DELETE_TASK_METER_BY_IDS, $taskMetricIds);
    }

    public function deleteTaskMetricsByRemovalTime(string $currentTimestamp, int $timeToLive, int $minuteFrom, int $minuteTo, int $batchSize): DbOperation
    {
        $parameters = [];
        // data inserted prior to now minus timeToLive-days can be removed
        $ut = (new \DateTime($currentTimestamp))->getTimestamp();
        $removalTime = $ut - $timeToLive * 86400;
        $parameters["removalTime"] = $removalTime;
        if ($minuteTo - $minuteFrom + 1 < 60) {
            $parameters["minuteFrom"] = $minuteFrom;
            $parameters["minuteTo"] = $minuteTo;
        }
        $parameters["batchSize"] = $batchSize;

        return $this->getDbEntityManager()
            ->deletePreserveOrder(
                TaskMeterLogEntity::class,
                self::DELETE_TASK_METER_BY_REMOVAL_TIME,
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }

    public function findTaskMetricsForCleanup(int $batchSize, int $timeToLive, int $minuteFrom, int $minuteTo): array
    {
        $queryParameters = [];
        $queryParameters["currentTimestamp"] = ClockUtil::getCurrentTime()->format('c');
        $queryParameters["timeToLive"] = $timeToLive;
        if ($minuteTo - $minuteFrom + 1 < 60) {
            $queryParameters["minuteFrom"] = $minuteFrom;
            $queryParameters["minuteTo"] = $minuteTo;
        }
        $parameterObject = new ListQueryParameterObject($queryParameters, 0, $batchSize);
        $parameterObject->addOrderingProperty(new QueryOrderingProperty(new QueryPropertyImpl("TIMESTAMP_"), Direction::ascending()));

        return $this->getDbEntityManager()->selectList(self::SELECT_TASK_METER_FOR_CLEANUP, $parameterObject);
    }
}
