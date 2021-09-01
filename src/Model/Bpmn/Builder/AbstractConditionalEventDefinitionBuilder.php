<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ConditionInterface,
    ConditionalEventDefinitionInterface
};

abstract class AbstractConditionalEventDefinitionBuilder extends AbstractRootElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ConditionalEventDefinitionInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function condition(string $conditionText): AbstractConditionalEventDefinitionBuilder
    {
        $condition = $this->createInstance(ConditionInterface::class);
        $condition->setTextContent($conditionText);
        $this->element->setCondition($condition);
        return $this;
    }

    public function variableName(string $variableName): AbstractConditionalEventDefinitionBuilder
    {
        $this->element->setVariableName($variableName);
        return $this;
    }

    /**
     * @param mixed $variableEvents
     */
    public function variableEvents($variableEvents): AbstractConditionalEventDefinitionBuilder
    {
        if (is_array($variableEvents)) {
            $this->element->setVariableEventsList($variableEvents);
        } elseif (is_string($variableEvents)) {
            $this->element->setVariableEvents($variableEvents);
        }
        return $this;
    }

    public function conditionalEventDefinitionDone(): AbstractFlowNodeBuilder
    {
        return $this->element->getParentElement()->builder();
    }
}
