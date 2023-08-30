<?php

namespace Jabe\Impl;

use Jabe\History\HistoricProcessInstanceQueryInterface;
use Jabe\Impl\Batch\BatchElementConfiguration;
use Jabe\Impl\Cmd\AbstractSetJobsRetriesBatchCmd;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Runtime\ProcessInstanceQueryInterface;

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
