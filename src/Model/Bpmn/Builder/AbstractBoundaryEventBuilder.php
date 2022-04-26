<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\Bpmndi\{
    BpmnEdgeInterface,
    BpmnShapeInterface
};
use Jabe\Model\Bpmn\Instance\Di\WaypointInterface;
use Jabe\Model\Bpmn\Instance\{
    BoundaryEventInterface,
    ErrorEventDefinitionInterface,
    EscalationEventDefinitionInterface,
    FlowNodeInterface
};

abstract class AbstractBoundaryEventBuilder extends AbstractCatchEventBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BoundaryEventInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function cancelActivity(bool $cancelActivity): AbstractBoundaryEventBuilder
    {
        $this->element->setCancelActivity($cancelActivity);
        return $this;
    }

    public function error(?string $errorCode = null, ?string $errorMessage = null): AbstractBoundaryEventBuilder
    {
        if ($errorCode == null && $errorMessage == null) {
            $errorEventDefinition = $this->createInstance(ErrorEventDefinitionInterface::class);
        } else {
            $errorEventDefinition = $this->createErrorEventDefinition($errorCode, $errorMessage);
        }
        $this->element->addEventDefinition($errorEventDefinition);
        return $this;
    }

    public function errorEventDefinition(?string $id = null): ErrorEventDefinitionBuilder
    {
        $errorEventDefinition = $this->createEmptyErrorEventDefinition();
        if ($id != null) {
            $errorEventDefinition->setId($id);
        }
        $this->element->addEventDefinition($errorEventDefinition);
        return new ErrorEventDefinitionBuilder($this->modelInstance, $errorEventDefinition);
    }

    public function escalation(?string $escalationCode = null): AbstractBoundaryEventBuilder
    {
        if ($escalationCode == null) {
            $escalationEventDefinition = $this->createInstance(EscalationEventDefinitionInterface::class);
        } else {
            $escalationEventDefinition = $this->createEscalationEventDefinition($escalationCode);
        }
        $this->element->addEventDefinition($escalationEventDefinition);
        return $this;
    }

    protected function setCoordinates(BpmnShapeInterface $shape): void
    {
        $source = $this->findBpmnShape($this->element);
        $shapeBounds = $shape->getBounds();

        $x = 0;
        $y = 0;

        if ($source != null) {
            $sourceBounds = $source->getBounds();

            $sourceX = $sourceBounds->getX();
            $sourceWidth = $sourceBounds->getWidth();
            $sourceY = $sourceBounds->getY();
            $sourceHeight = $sourceBounds->getHeight();
            $targetHeight = $shapeBounds->getHeight();

            $x = $sourceX + $sourceWidth + self::SPACE / 4;
            $y = $sourceY + $sourceHeight - $targetHeight / 2 + self::SPACE;
        }

        $shapeBounds->setX($x);
        $shapeBounds->setY($y);
    }

    protected function setWaypointsWithSourceAndTarget(
        BpmnEdgeInterface $edge,
        FlowNodeInterface $edgeSource,
        FlowNodeInterface $edgeTarget
    ): void {
        $source = $this->findBpmnShape($edgeSource);
        $target = $this->findBpmnShape($edgeTarget);

        if ($source != null && $target != null) {
            $sourceBounds = $source->getBounds();
            $targetBounds = $target->getBounds();

            $sourceX = $sourceBounds->getX();
            $sourceY = $sourceBounds->getY();
            $sourceWidth = $sourceBounds->getWidth();
            $sourceHeight = $sourceBounds->getHeight();

            $targetX = $targetBounds->getX();
            $targetY = $targetBounds->getY();
            $targetHeight = $targetBounds->getHeight();

            $w1 = $this->createInstance(WaypointInterface::class);
            $w1->setX($sourceX + $sourceWidth / 2);
            $w1->setY($sourceY + $sourceHeight);

            $w2 = $this->createInstance(WaypointInterface::class);
            $w2->setX($sourceX + $sourceWidth / 2);
            $w2->setY($sourceY + $sourceHeight + self::SPACE);

            $w3 = $this->createInstance(WaypointInterface::class);
            $w3->setX($targetX);
            $w3->setY($targetY + $targetHeight / 2);

            $edge->addChildElement($w1);
            $edge->addChildElement($w2);
            $edge->addChildElement($w3);
        }
    }
}
