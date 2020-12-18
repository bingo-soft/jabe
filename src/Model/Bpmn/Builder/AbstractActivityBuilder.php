<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\BpmnShapeInterface;
use BpmPlatform\Model\Bpmn\Instance\Dc\BoundsInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivityInterface,
    BoundaryEventInterface,
    InputOutputInterface,
    InputParameterInterface,
    MultiInstanceLoopCharacteristicsInterface,
    OutputParameterInterface
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

    public function boundaryEvent(string $id): BoundaryEventBuilder
    {
        $boundaryEvent = $this->createSibling(BoundaryEventInterface::class, $id);
        $boundaryEvent->setAttachedTo($element);
        $boundaryEventBpmnShape = $this->createBpmnShape($boundaryEvent);
        $this->setBoundaryEventCoordinates($boundaryEventBpmnShape);
        return $boundaryEvent->builder();
    }

    public function multiInstance(): MultiInstanceLoopCharacteristicsBuilder
    {
        $miCharacteristics = $this->createChild(MultiInstanceLoopCharacteristicsInterface::class);
        return $miCharacteristics->builder();
    }

    public function inputParameter(string $name, string $value): AbstractActivityBuilder
    {
        $inputOutput = $this->getCreateSingleExtensionElement(InputOutputInterface::class);
        $inputParameter = $this->createChild($inputOutput, InputParameterInterface::class);
        $inputParameter->setName($name);
        $inputParameter->setTextContent($value);
        return $this->myself;
    }

    public function outputParameter(string $name, string $value): AbstractActivityBuilder
    {
        $inputOutput = $this->getCreateSingleExtensionElement(InputOutputInterface::class);
        $inputParameter = $this->createChild($inputOutput, OutputParameterInterface::class);
        $inputParameter->setName($name);
        $inputParameter->setTextContent($value);
        return $this->myself;
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
                if ($tmp->getAttachedTo() == $this->element) {
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
            $x = $this->calculateXCoordinate($boundaryBounds);
            $y = $activityY + $activityHeight - $boundaryHeight / 2;
        }
        $boundaryBounds->setX($x);
        $boundaryBounds->setY($y);
    }
}
