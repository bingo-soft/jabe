<?php

namespace Jabe\Engine\Impl\JobExecutor;

use Jabe\Engine\Impl\Db\EntityManager\Cache\DbEntityCache;
use Jabe\Engine\Impl\Persistence\Entity\JobEntity;

class JobExecutorContext
{
    protected $currentProcessorJobQueue = [];

    /** the currently executed job */
    protected $currentJob;

    /** reusable cache */
    protected $entityCache;

    public function getCurrentProcessorJobQueue(): array
    {
        return $this->currentProcessorJobQueue;
    }

    public function isExecutingExclusiveJob(): bool
    {
        return $this->currentJob == null ? false : $this->currentJob->isExclusive();
    }

    public function setCurrentJob(JobEntity $currentJob): void
    {
        $this->currentJob = $currentJob;
    }

    public function getCurrentJob(): JobEntity
    {
        return $this->currentJob;
    }

    public function getEntityCache(): DbEntityCache
    {
        return $this->entityCache;
    }

    public function setEntityCache(DbEntityCache $entityCache): void
    {
        $this->entityCache = $entityCache;
    }
}
