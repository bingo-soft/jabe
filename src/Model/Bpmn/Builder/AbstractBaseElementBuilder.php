<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ActivityInterface,
    BpmnModelElementInstanceInterface,
    BaseElementInterface,
    CompensateEventDefinitionInterface,
    DocumentationInterface,
    ErrorEventDefinitionInterface,
    ErrorInterface,
    EscalationInterface,
    EscalationEventDefinitionInterface,
    EventInterface,
    ExclusiveGatewayInterface,
    ExtensionElementsInterface,
    FlowElementInterface,
    FlowNodeInterface,
    GatewayInterface,
    MessageInterface,
    MessageEventDefinitionInterface,
    SequenceFlowInterface,
    SignalInterface,
    SignalEventDefinitionInterface,
    SubProcessInterface,
    TimeCycleInterface,
    TimeDateInterface,
    TimeDurationInterface,
    TimerEventDefinitionInterface
};
use BpmPlatform\Model\Bpmn\Instance\Bpmndi\{
    BpmnShapeInterface,
    BpmnEdgeInterface,
    BpmnPlaneInterface
};
use BpmPlatform\Model\Bpmn\Instance\Dc\{
    BoundsInterface
};
use BpmPlatform\Model\Bpmn\Instance\Di\{
    WaypointInterface
};

abstract class AbstractBaseElementBuilder extends AbstractBpmnModelElementBuilder
{
    public const SPACE = 50;

    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        BaseElementInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    protected function createInstance(string $typeClass, ?string $identifier = null): BpmnModelElementInstanceInterface
    {
        $instance = $this->modelInstance->newInstance($typeClass);
        if ($identifier !== null) {
            $instance->setId($identifier);
            if ($instance instanceof FlowElementInterface) {
                $instance->setName($identifier);
            }
        }
        return $instance;
    }

    protected function createChild(
        ?BpmnModelElementInstanceInterface $parent,
        string $typeClass,
        ?string $identifier
    ): BpmnModelElementInstanceInterface {
        $instance = $this->createInstance($typeClass, $identifier);
        if ($parent == null) {
            $parent = $this->element;
        }
        $parent->addChildElement($instance);
        return $instance;
    }

    protected function createSibling(string $typeClass, ?string $identifier): BpmnModelElementInstanceInterface
    {
        $instance = $this->createInstance($typeClass, $identifier);
        $this->element->getParentElement()->addChildElement($instance);
        return $instance;
    }

    protected function getCreateSingleChild(
        ?BpmnModelElementInstanceInterface $parent,
        string $typeClass
    ): BpmnModelElementInstanceInterface {
        if ($parent == null) {
            $parent = $this->element;
        }
        $childrenOfType = $parent->getChildElementsByType($typeClass);
        if (empty($childrenOfType)) {
            return $this->createChild($parent, $typeClass);
        } else {
            if (count($childrenOfType) > 1) {
                throw new BpmnModelException(
                    sprintf(
                        "Element of type %s has more than one child element of type %s",
                        $parent->getElementType()->getTypeName(),
                        $typeClass
                    )
                );
            } else {
                return $childrenOfType[0];
            }
        }
    }

    protected function getCreateSingleExtensionElement(string $typeClass): BpmnModelElementInstanceInterface
    {
        $extensionElements = $this->getCreateSingleChild(null, ExtensionElementsInterface::class);
        return $this->getCreateSingleChild($extensionElements, $typeClass);
    }

    protected function findMessageForName(string $messageName): MessageInterface
    {
        $messages = $this->modelInstance->getModelElementsByType(MessageInterface::class);
        foreach ($messages as $message) {
            if ($messageName == $message->getName()) {
                return $message;
            }
        }

        $definitions = $this->modelInstance->getDefinitions();
        $message = $this->createChild($definitions, MessageInterface::class);
        $message->setName($messageName);
        return $message;
    }

    protected function createMessageEventDefinition(string $messageName): MessageEventDefinitionInterface
    {
        $message = $this->findMessageForName($messageName);
        $messageEventDefinition = $this->createInstance(MessageEventDefinitionInterface::class);
        $messageEventDefinition->setMessage($message);
        return $messageEventDefinition;
    }

    protected function createEmptyMessageEventDefinition(): MessageEventDefinitionInterface
    {
        return $this->createInstance(MessageEventDefinitionInterface::class);
    }

    protected function findSignalForName(string $signalName): SignalInterface
    {
        $signals = $this->modelInstance->getModelElementsByType(SignalInterface::class);
        foreach ($signals as $signal) {
            if ($signal->getName() == $signalName) {
                return $signal;
            }
        }

        $definitions = $this->modalInstance->getDefinitions();
        $signal = $this->createChild($definitions, SignalInterface::class);
        $signal->setName($signalName);

        return $signal;
    }

    protected function createSignalEventDefinition(string $signalName): SignalEventDefinitionInterface
    {
        $signal = $this->findSignalForName($signalName);
        $signalEventDefinition = $this->createInstance(SignalEventDefinitionInterface::class);
        $signalEventDefinition->setSignal($signal);
        return $signalEventDefinition;
    }

    protected function findErrorDefinitionForCode(string $errorCode): ?ErrorEventDefinitionInterface
    {
        $definitions = $this->modelInstance->getModelElementsByType(ErrorEventDefinitionInterface::class);
        foreach ($definitions as $definition) {
            $error = $definition->getError();
            if ($error != null && $error->getErrorCode() == $errorCode) {
                return $definition;
            }
        }
        return null;
    }

    protected function findErrorForNameAndCode(string $errorCode, ?string $errorMessage): ErrorInterface
    {
        $errors = $this->modelInstance->getModelElementsByType(ErrorInterface::class);
        foreach ($errors as $error) {
            if ($errorCode == $error->getErrorCode()) {
                return $error;
            }
        }

        $definitions = $this->modelInstance->getDefinitions();
        $error = $this->createChild($definitions, ErrorInterface::class);
        $error->setErrorCode($errorCode);
        if (!empty($errorMessage)) {
            $error->setErrorMessage($errorMessage);
        }

        return $error;
    }

    protected function createEmptyErrorEventDefinition(
        ?string $errorCode,
        ?string $errorMessage
    ): EscalationEventDefinitionInterface {
        if (empty($errorCode) && empty($errorMessage)) {
            $errorEventDefinition = $this->createInstance(EscalationEventDefinitionInterface::class);
            return $errorEventDefinition;
        }
        $error = $this->findErrorForNameAndCode($errorCode, $errorMessage);
        $errorEventDefinition = $this->createInstance(EscalationEventDefinitionInterface::class);
        $errorEventDefinition->setError($error);
        return $errorEventDefinition;
    }

    protected function findEscalationForCode(string $escalationCode): EscalationInterface
    {
        $escalations = $this->modelInstance->getModelElementsByType(EscalationInterface::class);
        foreach ($escalations as $escalation) {
            if ($escalationCode == $escalation->getEscalationCode()) {
                return $escalation;
            }
        }

        $definitions = $this->modelInstance->getDefinitions();
        $escalation = $this->createChild($definitions, EscalationInterface::class);
        $escalation->setEscalationCode($escalationCode);
        return $escalation;
    }

    protected function createEscalationEventDefinition(string $escalationCode): EscalationEventDefinitionInterface
    {
        $escalation = $this->findEscalationForCode($escalationCode);
        $escalationEventDefinition = $this->createInstance(EscalationEventDefinitionInterface::class);
        $escalationEventDefinition->setEscalation($escalation);
        return $escalationEventDefinition;
    }

    protected function createCompensateEventDefinition(): CompensateEventDefinitionInterface
    {
        $compensateEventDefinition = $this->createInstance(CompensateEventDefinitionInterface::class);
        return $compensateEventDefinition;
    }

    public function id(string $identifier): AbstractBaseElementBuilder
    {
        $this->element->setId($identifier);
        return $this;
    }

    public function documentation(string $documentation): AbstractBaseElementBuilder
    {
        $child = $this->createChild($this->element, DocumentationInterface::class);
        $child->setTextContext($documentation);
        return $this;
    }

    public function addExtensionElement(BpmnModelElementInstanceInterface $extensionElement): AbstractBaseElementBuilder
    {
        $extensionElements = $this->getCreateSingleChild(ExtensionElementsInterface::class);
        $extensionElements->addChildElement($extensionElement);
        return $this;
    }

    public function createBpmnShape(FlowNodeInterface $node): ?BpmnShapeInterface
    {
        $bpmnPlane = $this->findBpmnPlane();
        if ($bpmnPlane != null) {
            $bpmnShape = $this->createInstance(BpmnShapeInterface::class);
            $bpmnShape->setBpmnElement($node);
            $nodeBounds = $this->createInstance(BoundsInterface::class);

            if ($node instanceof SubProcessInterface) {
                $bpmnShape->setExpanded(true);
                $nodeBounds->setWidth(350);
                $nodeBounds->setHeight(200);
            } elseif ($node instanceof ActivityInterface) {
                $nodeBounds->setWidth(100);
                $nodeBounds->setHeight(80);
            } elseif ($node instanceof EventInterface) {
                $nodeBounds->setWidth(36);
                $nodeBounds->setHeight(36);
            } elseif ($node instanceof GatewayInterface) {
                $nodeBounds->setWidth(50);
                $nodeBounds->setHeight(50);
                if ($node instanceof ExclusiveGatewayInterface) {
                    $bpmnShape->setMarkerVisible(true);
                }
            }

            $nodeBounds->setX(0);
            $nodeBounds->setY(0);

            $bpmnShape->addChildElement($nodeBounds);
            $bpmnPlane->addChildElement($bpmnShape);

            return $bpmnShape;
        }
        return null;
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
            $x = $sourceX + $sourceWidth + self::SPACE;

            if ($this->element instanceof FlowNodeInterface) {
                $flowNode = $this->element;
                $outgoing = $flowNode->getOutgoing();
                if (count($outgoing) == 0) {
                    $sourceY = $sourceBounds->getY();
                    $sourceHeight = $sourceBounds->getHeight();
                    $targetHeight = $shapeBounds->getHeight();
                    $y = $sourceY + $sourceHeight / 2 - $targetHeight / 2;
                } else {
                    $last = $outgoing[count($outgoing) - 1];
                    $targetShape = $this->findBpmnShape($last->getTarget());
                    if ($targetShape != null) {
                        $targetBounds = $targetShape->getBounds();
                        $lastY = $targetBounds->getY();
                        $lastHeight = $targetBounds->getHeight();
                        $y = $lastY + $lastHeight + self::SPACE;
                    }
                }
            }
        }
        $shapeBounds->setX($x);
        $shapeBounds->setY($y);
    }

    public function createEdge(BaseElementInterface $baseElement): ?BpmnEdgeInterface
    {
        $bpmnPlane = $this->findBpmnPlane();
        if ($bpmnPlane != null) {
            $edge = $this->createInstance(BpmnEdgeInterface::class);
            $edge->setBpmnElement($baseElement);
            $this->setWaypoints($edge);
            $bpmnPlane->addChildElement($edge);
            return $edge;
        }
        return null;
    }

    protected function setWaypoints(BpmnEdgeInterface $edge): void
    {
        $bpmnElement = $edge->getBpmnElement();
        if ($bpmnElement instanceof SequenceFlowInterface || $bpmnElement instanceof AssociationInterface) {
            $edgeSource = $bpmnElement->getSource();
            $edgeTarget = $bpmnElement->getTarget();
        } else {
            throw new \Exception("Bpmn element type not supported");
        }
        $this->setWaypointsWithSourceAndTarget($edge, $edgeSource, $edgeTarget);
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

            if (count($edgeSource->getOutgoing()) == 1) {
                $w1->setX($sourceX + $sourceWidth);
                $w1->setY($sourceY + $sourceHeight / 2);

                $edge->addChildElement($w1);
            } else {
                $w1->setX($sourceX + $sourceWidth / 2);
                $w1->setY($sourceY + $sourceHeight);

                $edge->addChildElement($w1);

                $w2 = $this->createInstance(WaypointInterface::class);
                $w2->setX($sourceX + $sourceWidth / 2);
                $w2->setY($targetY + $targetHeight / 2);

                $edge->addChildElement(w2);
            }

            $w3 = $this->createInstance(WaypointInterface::class);
            $w3->setX($targetX);
            $w3->setY($targetY + $targetHeight / 2);

            $edge->addChildElement($w3);
        }
    }

    protected function findBpmnPlane(): ?BpmnPlaneInterface
    {
        $planes = $this->modelInstance->getModelElementsByType(BpmnPlaneInterface::class);
        return !empty($planes) ? $planes[0] : null;
    }

    protected function findBpmnShape(BaseElementInterface $node): ?BpmnShapeInterface
    {
        $allShapes = $this->modelInstance->getModelElementsByType(BpmnShapeInterface::class);
        foreach ($allShapes as $shape) {
            if ($shape->getBpmnElement()->equals($node)) {
                return $shape;
            }
        }
        return null;
    }

    protected function findBpmnEdge(BaseElementInterface $sequenceFlow): ?BpmnEdgeInterface
    {
        $allEdges = $this->modelInstance->getModelElementsByType(BpmnEdgeInterface::class);
        foreach ($allEdges as $edge) {
            if ($edge->getBpmnElement()->equals($sequenceFlow)) {
                return $edge;
            }
        }
        return null;
    }

    protected function resizeSubProcess(BpmnShapeInterface $innerShape): void
    {
        $innerElement = $innerShape->getBpmnElement();
        $innerShapeBounds = $innerShape->getBounds();

        $parent = $innerElement->getParentElement();

        while ($parent instanceof SubProcessInterface) {
            $subProcessShape = $this->findBpmnShape($parent);

            if ($subProcessShape != null) {
                $subProcessBounds = $subProcessShape->getBounds();
                $innerX = $innerShapeBounds->getX();
                $innerWidth = $innerShapeBounds->getWidth();
                $innerY = $innerShapeBounds->getY();
                $innerHeight = $innerShapeBounds->getHeight();

                $subProcessY = $subProcessBounds->getY();
                $subProcessHeight = $subProcessBounds->getHeight();
                $subProcessX = $subProcessBounds->getX();
                $subProcessWidth = $subProcessBounds->getWidth();

                $tmpWidth = $innerX + $innerWidth + self::SPACE;
                $tmpHeight = $innerY + $innerHeight + self::SPACE;

                if ($innerY == $subProcessY) {
                    $subProcessBounds->setY($subProcessY - self::SPACE);
                    $subProcessBounds->setHeight($subProcessHeight + self::SPACE);
                }

                if ($tmpWidth >= $subProcessX + $subProcessWidth) {
                    $newWidth = $tmpWidth - $subProcessX;
                    $subProcessBounds->setWidth($newWidth);
                }

                if ($tmpHeight >= $subProcessY + $subProcessHeight) {
                    $newHeight = $tmpHeight - $subProcessY;
                    $subProcessBounds->setHeight($newHeight);
                }

                $innerElement = $parent;
                $innerShapeBounds = $subProcessBounds;
                $parent = $innerElement->getParentElement();
            } else {
                break;
            }
        }
    }

    protected function createTimeCycle(string $timerCycle): TimerEventDefinitionInterface
    {
        $timeCycle = $this->createInstance(TimeCycleInterface::class);
        $timeCycle->setTextContent($timerCycle);
        $timerDefinition = $this->createInstance(TimerEventDefinitionInterface::class);
        $timerDefinition->setTimeCycle($timeCycle);
        return $timerDefinition;
    }

    protected function createTimeDate(string $timerDate): TimerEventDefinitionInterface
    {
        $timeDate = $this->createInstance(TimeDateInterface::class);
        $timeDate->setTextContent($timerDate);
        $timerDefinition = $this->createInstance(TimerEventDefinitionInterface::class);
        $timerDefinition->setTimeDate($timeDate);
        return $timerDefinition;
    }

    protected function createTimeDuration(string $timerDuration): TimerEventDefinitionInterface
    {
        $timeDuration = $this->createInstance(TimeDurationInterface::class);
        $timeDuration->setTextContent($timerDuration);
        $timerDefinition = $this->createInstance(TimerEventDefinitionInterface::class);
        $timerDefinition->setTimeDuration($timeDuration);
        return $timerDefinition;
    }
}
