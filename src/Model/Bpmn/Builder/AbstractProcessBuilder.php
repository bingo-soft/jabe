<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ProcessInterface;

abstract class AbstractProcessBuilder extends AbstractCallableElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ParallelGatewayInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function processType(string $processType): AbstractProcessBuilder
    {
        $this->element->setProcessType($processType);
        return $this->myself;
    }

    public function closed(): AbstractProcessBuilder
    {
        $this->element->setClosed(true);
        return $this->myself;
    }

    public function executable(): AbstractProcessBuilder
    {
        $this->element->setExecutable(true);
        return $this->myself;
    }

    public function jobPriority(string $jobPriority): AbstractProcessBuilder
    {
        $this->element->setJobPriority($jobPriority);
        return $this->myself;
    }

    public function taskPriority(string $taskPriority): AbstractProcessBuilder
    {
        $this->element->setTaskPriority($taskPriority);
        return $this->myself;
    }

    public function historyTimeToLive(int $historyTimeToLive): AbstractProcessBuilder
    {
        $this->element->setHistoryTimeToLive($historyTimeToLive);
        return $this->myself;
    }

    public function startableInTasklist(bool $isStartableInTasklist): AbstractProcessBuilder
    {
        $this->element->setIsStartableInTasklist($isStartableInTasklist);
        return $this->myself;
    }

    public function versionTag(string $versionTag): AbstractProcessBuilder
    {
        $this->element->setVersionTag($versionTag);
        return $this->myself;
    }
}
