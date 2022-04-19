<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\JobQueryImpl;
use BpmPlatform\Engine\Impl\Batch\BatchElementConfiguration;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Util\CollectionUtil;
use BpmPlatform\Engine\Runtime\JobQueryInterface;

class SetJobsRetriesBatchCmd extends AbstractSetJobsRetriesBatchCmd
{
    protected $ids = [];
    protected $jobQuery;

    public function __construct(array $ids, JobQueryInterface $jobQuery, int $retries)
    {
        $this->jobQuery = $jobQuery;
        $this->ids = $ids;
        $this->retries = $retries;
    }

    protected function collectJobIds(CommandContext $commandContext): BatchElementConfiguration
    {
        $elementConfiguration = new BatchElementConfiguration();

        if (!CollectionUtil::isEmpty($this->ids)) {
            $query = new JobQueryImpl();
            $query->jobIds($this->ids);
            $elementConfiguration->addDeploymentMappings(
                $commandContext->runWithoutAuthorization(function () use ($query) {
                    return $query->listDeploymentIdMappings();
                }),
                $this->ids
            );
        }

        if ($this->jobQuery != null) {
            $elementConfiguration->addDeploymentMappings($jobQuery->listDeploymentIdMappings());
        }

        return $elementConfiguration;
    }
}
