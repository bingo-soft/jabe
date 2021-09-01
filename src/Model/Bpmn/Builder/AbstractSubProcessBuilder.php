<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\SubProcessInterface;

abstract class AbstractSubProcessBuilder extends AbstractActivityBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SubProcessInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function embeddedSubProcess(): EmbeddedSubProcessBuilder
    {
        return new EmbeddedSubProcessBuilder($this);
    }

    public function triggerByEvent(): AbstractSubProcessBuilder
    {
        $this->element->setTriggeredByEvent(true);
        return $this;
    }
}
