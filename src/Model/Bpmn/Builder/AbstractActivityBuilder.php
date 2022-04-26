<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;
use Jabe\Model\Bpmn\Instance\Dc\BoundsInterface;
use Jabe\Model\Bpmn\Instance\Extension\{
    InputOutputInterface,
    InputParameterInterface,
    OutputParameterInterface
};
use Jabe\Model\Bpmn\Instance\{
    ActivityInterface,
    BoundaryEventInterface,
    MultiInstanceLoopCharacteristicsInterface
};

abstract class AbstractActivityBuilder extends AbstractFlowNodeBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ActivityInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function boundaryEvent(?string $id = null): BoundaryEventBuilder
    {
        $boundaryEvent = $this->createSibling(BoundaryEventInterface::class, $id);
        $boundaryEvent->setAttachedTo($this->element);
        $boundaryEventBpmnShape = $this->createBpmnShape($boundaryEvent);
        $this->setBoundaryEventCoordinates($boundaryEventBpmnShape);
        return $boundaryEvent->builder();
    }

    public function multiInstance(): MultiInstanceLoopCharacteristicsBuilder
    {
        $miCharacteristics = $this->createChild(null, MultiInstanceLoopCharacteristicsInterface::class);
        return $miCharacteristics->builder();
    }

    public function inputParameter(string $name, string $value): AbstractActivityBuilder
    {
        $inputOutput = $this->getCreateSingleExtensionElement(InputOutputInterface::class);
        $inputParameter = $this->createChild($inputOutput, InputParameterInterface::class);
        $inputParameter->setName($name);
        $inputParameter->setTextContent($value);
        return $this;
    }

    public function outputParameter(string $name, string $value): AbstractActivityBuilder
    {
        $inputOutput = $this->getCreateSingleExtensionElement(InputOutputInterface::class);
        $inputParameter = $this->createChild($inputOutput, OutputParameterInterface::class);
        $inputParameter->setName($name);
        $inputParameter->setTextContent($value);
        return $this;
    }

    protected function calculateXCoordinate(BoundsInterface $boundaryEventBounds): float
    {
        $attachedToElement = $this->findBpmnShape($this->element);
        $x = 0;

        if ($attachedToElement != null) {
            $attachedToBounds = $attachedToElement->getBounds();

            $boundaryEvents = $this->element->getParentElement()->getChildElementsByType(BoundaryEventInterface::class);
            $attachedBoundaryEvents = [];
            foreach ($boundaryEvents as $tmp) {
                if ($tmp->getAttachedTo()->equals($this->element)) {
                    $attachedBoundaryEvents[] = $tmp;
                }
            }

            $attachedToX = $attachedToBounds->getX();
            $attachedToWidth = $attachedToBounds->getWidth();
            $boundaryWidth = $boundaryEventBounds->getWidth();

            switch (count($attachedBoundaryEvents)) {
                case 2:
                    $x = $attachedToX + $attachedToWidth / 2 + $boundaryWidth / 2;
                    break;
                case 3:
                    $x = $attachedToX + $attachedToWidth / 2 - 1.5 * $boundaryWidth;
                    break;
                default:
                    $x = $attachedToX + $attachedToWidth / 2 - $boundaryWidth / 2;
                    break;
            }
        }

        return $x;
    }

    protected function setBoundaryEventCoordinates(BpmnShapeInterface $bpmnShape): void
    {
        $activity = $this->findBpmnShape($this->element);
        $boundaryBounds = $bpmnShape->getBounds();
        $x = 0;
        $y = 0;
        if ($activity != null) {
            $activityBounds = $activity->getBounds();
            $activityY = $activityBounds->getY();
            $activityHeight = $activityBounds->getHeight();
            $boundaryHeight = $boundaryBounds->getHeight();
            $x = $this->calculateXCoordinate($boundaryBounds);
            $y = $activityY + $activityHeight - $boundaryHeight / 2;
        }
        $boundaryBounds->setX($x);
        $boundaryBounds->setY($y);
    }
}
