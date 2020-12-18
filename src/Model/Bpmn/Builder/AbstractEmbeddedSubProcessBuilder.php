<?php

namespace BpmPlatform\Model\Bpmn\Builder;

abstract class AbstractEmbeddedSubProcessBuilder
{
    protected $subProcessBuilder;
    protected $myself;

    protected function __construct(AbstractEmbeddedSubProcessBuilder $subProcessBuilder, string $selfType)
    {
        $this->myself = $this;
        $this->subProcessBuilder = $subProcessBuilder;
    }
}
