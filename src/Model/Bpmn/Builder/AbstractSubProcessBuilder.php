<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\SubProcessInterface;

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
