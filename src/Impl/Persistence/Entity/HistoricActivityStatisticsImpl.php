<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\History\HistoricActivityStatisticsInterface;

class HistoricActivityStatisticsImpl implements HistoricActivityStatisticsInterface
{
    protected $id;
    protected int $instances = 0;
    protected int $finished = 0;
    protected int $canceled = 0;
    protected int $completeScope = 0;
    protected int $openIncidents = 0;
    protected int $resolvedIncidents = 0;
    protected int $deletedIncidents = 0;

    /**
     * The activity id.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * The number of all running instances of the activity.
     */
    public function getInstances(): int
    {
        return $this->instances;
    }

    public function setInstances(int $instances): void
    {
        $this->instances = $instances;
    }

    /**
     * The number of all finished instances of the activity.
     */
    public function getFinished(): int
    {
        return $this->finished;
    }

    public function setFinished(int $finished): void
    {
        $this->finished = $finished;
    }

    /**
     * The number of all canceled instances of the activity.
     */
    public function getCanceled(): int
    {
        return $this->canceled;
    }

    public function setCanceled(int $canceled): void
    {
        $this->canceled = $canceled;
    }

    /**
     * The number of all instances, which complete a scope (ie. in bpmn manner: an activity
     * which consumed a token and did not produced a new one), of the activity.
     */
    public function getCompleteScope(): int
    {
        return $this->completeScope;
    }

    public function setCompleteScope(int $completeScope): void
    {
        $this->completeScope = $completeScope;
    }

    /** The number of open incidents of the activity. */
    public function getOpenIncidents(): int
    {
        return $this->openIncidents;
    }

    public function setOpenIncidents(int $openIncidents): void
    {
        $this->openIncidents = $openIncidents;
    }

    /** The number of resolved incidents of the activity. */
    public function getResolvedIncidents(): int
    {
        $this->resolvedIncidents = $resolvedIncidents;
    }

    public function setResolvedIncidents(int $resolvedIncidents): void
    {
        $this->resolvedIncidents = $resolvedIncidents;
    }

    /** The number of deleted incidents of the activity. */
    public function getDeletedIncidents(): int
    {
        $this->deletedIncidents = $deletedIncidents;
    }

    public function setDeletedIncidents(int $deletedIncidents): void
    {
        $this->deletedIncidents = $deletedIncidents;
    }
}
