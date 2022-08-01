<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\History\HistoricProcessInstanceQueryInterface;
use Jabe\Engine\Impl\Batch\BatchElementConfiguration;
use Jabe\Engine\Impl\Cmd\AbstractSetJobsRetriesBatchCmd;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Runtime\ProcessInstanceQueryInterface;

class SetJobsRetriesByProcessBatchCmd extends AbstractSetJobsRetriesBatchCmd
{
    protected $processInstanceIds = [];
    protected $query;
    protected $historicProcessInstanceQuery;

    public function __construct(
        array $processInstanceIds,
        ?ProcessInstanceQueryInterface $query,
        ?HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery,
        int $retries
    ) {
        $this->processInstanceIds = $processInstanceIds;
        $this->query = $query;
        $this->historicProcessInstanceQuery = $historicProcessInstanceQuery;
        $this->retries = $retries;
    }

    protected function collectJobIds(CommandContext $commandContext): BatchElementConfiguration
    {
        $collectedProcessInstanceIds = [];

        if ($this->query !== null) {
            $collectedProcessInstanceIds = array_merge($collectedProcessInstanceIds, $this->query->listIds());
        }

        if ($this->historicProcessInstanceQuery !== null) {
            $collectedProcessInstanceIds = array_merge($collectedProcessInstanceIds, $this->historicProcessInstanceQuery->listIds());
        }

        if (!empty($this->processInstanceIds)) {
            $collectedProcessInstanceIds = array_merge($collectedProcessInstanceIds, $this->processInstanceIds);
        }

        $elementConfiguration = new BatchElementConfiguration();

        if (!empty($collectedProcessInstanceIds)) {
            $jobQuery = new JobQueryImpl();
            $jobQuery->processInstanceIds($collectedProcessInstanceIds);
            $elementConfiguration->addDeploymentMappings($commandContext->runWithoutAuthorization(function () use ($jobQuery) {
                return $jobQuery->listDeploymentIdMappings();
            }));
        }

        return $elementConfiguration;
    }
}
