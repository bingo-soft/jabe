<?php

namespace Jabe\Impl\Metrics;

use Jabe\ProcessEngineException;
use Jabe\Impl\Db\ListQueryParameterObject;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Impl\Metrics\Util\MetricsUtil;
use Jabe\Management\{
    MetricIntervalValueInterface,
    MetricsQueryInterface
};

class MetricsQueryImpl extends ListQueryParameterObject implements \Serializable, CommandInterface, MetricsQueryInterface
{
    public const DEFAULT_LIMIT_SELECT_INTERVAL = 200;
    public const DEFAULT_SELECT_INTERVAL = 15 * 60;

    protected $name;
    protected $reporter;
    protected $startDate;
    protected $endDate;
    protected $startDateMilliseconds;
    protected $endDateMilliseconds;
    protected $interval;
    protected $aggregateByReporter;
    protected $commandExecutor;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct();
        $this->commandExecutor = $commandExecutor;
        $this->maxResults = self::DEFAULT_LIMIT_SELECT_INTERVAL;
        $this->interval = self::DEFAULT_SELECT_INTERVAL;
    }

    public function serialize()
    {
        return json_encode([
            'name' => $this->name,
            'reporter' => $this->reporter,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'startDateMilliseconds' => $this->startDateMilliseconds,
            'endDateMilliseconds' => $this->endDateMilliseconds,
            'interval' => $this->interval,
            'aggregateByReporter' => $this->aggregateByReporter
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->name = $json->name;
        $this->reporter = $json->reporter;
        $this->startDate = $json->startDate;
        $this->endDate = $json->endDate;
        $this->startDateMilliseconds = $json->startDateMilliseconds;
        $this->endDateMilliseconds = $json->endDateMilliseconds;
        $this->interval = $json->interval;
        $this->aggregateByReporter = $json->aggregateByReporter;
    }

    public function name(?string $name): MetricsQueryImpl
    {
        $this->name = MetricsUtil::resolveInternalName($name);
        return $this;
    }

    public function reporter(?string $reporter): MetricsQueryInterface
    {
        $this->reporter = $reporter;
        return $this;
    }

    public function startDate(?string $startDate): MetricsQueryImpl
    {
        $this->startDate = $startDate;
        $this->startDateMilliseconds = (new \DateTime($startDate))->getTimestamp() * 1000;
        return $this;
    }

    public function endDate(?string $endDate): MetricsQueryImpl
    {
        $this->endDate = $endDate;
        $this->endDateMilliseconds = (new \DateTime($endDate))->getTimestamp() * 1000;
        return $this;
    }

    /**
     * Contains the command implementation which should be executed either
     * metric sum or select metric grouped by time interval.
     *
     * Note: this enables to quit with the enum distinction
     */
    protected $callback;

    public function interval(int $interval = null): array
    {
        if ($interval !== null) {
            $this->interval = $interval;
        }
        $this->callback = new MetricsQueryIntervalCmd($this);
        return $this->commandExecutor->execute($this);
    }

    public function sum(): int
    {
        $this->callback = new MetricsQuerySumCmd($this);
        return $this->commandExecutor->execute($this);
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        if ($this->callback !== null) {
            return $this->callback->execute($commandContext);
        }
        throw new ProcessEngineException("Query can't be executed. Use either sum or interval to query the metrics.");
    }

    public function offset(int $offset): MetricsQueryInterface
    {
        $this->setFirstResult($offset);
        return $this;
    }

    public function limit(int $maxResults): MetricsQueryInterface
    {
        $this->setMaxResults($maxResults);
        return $this;
    }

    public function aggregateByReporter(): MetricsQueryInterface
    {
        $this->aggregateByReporter = true;
        return $this;
    }

    public function setMaxResults(int $maxResults): void
    {
        if ($maxResults > self::DEFAULT_LIMIT_SELECT_INTERVAL) {
            throw new ProcessEngineException("Metrics interval query row limit can't be set larger than " . self::DEFAULT_LIMIT_SELECT_INTERVAL . '.');
        }
        $this->maxResults = $maxResults;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function getStartDateMilliseconds(): int
    {
        return $this->startDateMilliseconds;
    }

    public function getEndDateMilliseconds(): int
    {
        return $this->endDateMilliseconds;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getReporter(): ?string
    {
        return $this->reporter;
    }

    public function getInterval(): int
    {
        if ($this->interval == null) {
            return self::DEFAULT_SELECT_INTERVAL;
        }
        return $this->interval;
    }

    public function getMaxResults(): int
    {
        if ($this->maxResults > self::DEFAULT_LIMIT_SELECT_INTERVAL) {
            return self::DEFAULT_LIMIT_SELECT_INTERVAL;
        }
        return parent::getMaxResults();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
