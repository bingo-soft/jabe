<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    EventSubProcessInterface,
    StartEventInterface
};

abstract class AbstractEventSubProcessBuilder extends AbstractFlowElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        EventSubProcessInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function startEvent(?string $id = null): StartEventBuilder
    {
        $start = $this->createChild(StartEventInterface::class, $id);

        $startShape = $this->createBpmnShape($start);
        $subProcessShape = $this->findBpmnShape($this->getElement());

        if ($subProcessShape != null) {
            $subProcessBounds = $subProcessShape->getBounds();
            $startBounds = $startShape->getBounds();

            $subProcessX = $subProcessBounds->getX();
            $subProcessY = $subProcessBounds->getY();
            $subProcessHeight = $subProcessBounds->getHeight();
            $startHeight = $startBounds->getHeight();
            $startBounds->setX($subProcessX + self::SPACE);
            $startBounds->setY($subProcessY + $subProcessHeight / 2 - $startHeight / 2);
        }
    }
}
