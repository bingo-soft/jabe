<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\Exception\BpmnModelException;
use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\{
    CompensateEventDefinitionInterface
};

abstract class AbstractCompensateEventDefinitionBuilder extends AbstractRootElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        CompensateEventDefinitionInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function activityRef(string $activityId): AbstractCompensateEventDefinitionBuilder
    {
        $activity = $this->modelInstance->getModelElementById($activityId);
        if ($activity === null) {
            throw new BpmnModelException(sprintf("Activity with id '%s' does not exist", $activityId));
        }
        $event = $this->element->getParentElement();
        if ($activity->getParentElement() != $event->getParentElement()) {
            throw new BpmnModelException(
                sprintf("Activity with id '%s' must be in the same scope as '%s'", $activityId, $event->getId())
            );
        }
        $this->element->setActivity($activity);
        return $this;
    }

    public function waitForCompletion(bool $waitForCompletion): AbstractCompensateEventDefinitionBuilder
    {
        $this->element->setWaitForCompletion($waitForCompletion);
        return $this;
    }

    public function compensateEventDefinitionDone(): AbstractFlowNodeBuilder
    {
        return $this->element->getParentElement()->builder();
    }
}
