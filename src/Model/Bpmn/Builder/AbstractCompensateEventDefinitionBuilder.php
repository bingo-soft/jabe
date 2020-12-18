<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
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
        if ($activity == null) {
            throw new BpmnModelException(sprintf("Activity with id '%s' does not exist", $activityId));
        }
        $event = $this->element->getParentElement();
        if ($activity->getParentElement() != $event->getParentElement()) {
            throw new BpmnModelException(
                sprintf("Activity with id '%s' must be in the same scope as '%s'", $activityId, $event->getId())
            );
        }
        $this->element->setActivity($activity);
        return $this->myself;
    }

    public function waitForCompletion(bool $waitForCompletion): AbstractCompensateEventDefinitionBuilder
    {
        $this->element->setWaitForCompletion($waitForCompletion);
        return $this->myself;
    }

    public function compensateEventDefinitionDone(): AbstractFlowNodeBuilder
    {
        return $this->element->getParentElement()->builder();
    }
}
