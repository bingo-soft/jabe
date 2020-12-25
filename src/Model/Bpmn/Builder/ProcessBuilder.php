<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ProcessInterface,
    StartEventInterface
};

class ProcessBuilder extends AbstractProcessBuilder
{
    public function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ProcessInterface $element
    ) {
        parent::__construct($modelInstance, $element, ProcessBuilder::class);
    }

    public function startEvent(string $id): StartEventBuilder
    {
        $start = $this->createChild(StartEventInterface::class, $id);
        $bpmnShape = $this->createBpmnShape($start);
        $this->setCoordinates($bpmnShape);
        return $start->builder();
    }

    public function eventSubProcess(string $id): EventSubProcessBuilder
    {
        $subProcess = $this->createChild(SubProcessInterface::class, $id);
        $subProcess->setTriggeredByEvent(true);

        // Create Bpmn shape so subprocess will be drawn
        $targetBpmnShape = $this->createBpmnShape($subProcess);
        //find the lowest shape in the process
        // place event sub process underneath
        $this->setEventSubProcessCoordinates($targetBpmnShape);

        $this->resizeSubProcess($targetBpmnShape);

        // Return the eventSubProcessBuilder
        $eventSubProcessBuilder = new EventSubProcessBuilder($this->modelInstance, $subProcess);
        return $eventSubProcessBuilder;
    }

    protected function setCoordinates(BpmnShapeInterface $targetBpmnShape): void
    {
        $bounds = $targetBpmnShape->getBounds();
        $bounds->setX(100);
        $bounds->setY(100);
    }

    protected function setEventSubProcessCoordinates(BpmnShapeInterface $targetBpmnShape): void
    {
        $eventSubProcess = $targetBpmnShape->getBpmnElement();
        $targetBounds = $targetBpmnShape->getBounds();
        $lowestheight = 0;

        // find the lowest element in the model
        $allShapes = $this->modelInstance->getModelElementsByType(BpmnShapeInterface::class);
        foreach ($allShapes as $shape) {
            $bounds = $shape->getBounds();
            $bottom = $bounds->getY() + $bounds->getHeight();
            if ($bottom > $lowestheight) {
                $lowestheight = $bottom;
            }
        }

        $ycoord = $lowestheight + 50.0;
        $xcoord = 100.0;

        // move target
        $targetBounds->setY($ycoord);
        $targetBounds->setX($xcoord);
    }
}
