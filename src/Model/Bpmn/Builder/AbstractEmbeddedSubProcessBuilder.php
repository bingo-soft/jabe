<?php

namespace BpmPlatform\Model\Bpmn\Builder;

abstract class AbstractEmbeddedSubProcessBuilder
{
    protected $subProcessBuilder;

    protected function __construct(AbstractEmbeddedSubProcessBuilder $subProcessBuilder, string $selfType)
    {
        $this->subProcessBuilder = $subProcessBuilder;
    }
}
