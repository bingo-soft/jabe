<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\JobQueryImpl;
use Jabe\Engine\Impl\Batch\BatchElementConfiguration;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Util\CollectionUtil;
use Jabe\Engine\Runtime\JobQueryInterface;

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

        if ($this->jobQuery !== null) {
            $elementConfiguration->addDeploymentMappings($jobQuery->listDeploymentIdMappings());
        }

        return $elementConfiguration;
    }
}
