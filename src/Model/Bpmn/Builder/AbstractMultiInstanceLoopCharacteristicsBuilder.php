<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    CompletionConditionInterface,
    LoopCardinalityInterface,
    MultiInstanceLoopCharacteristicsInterface
};

abstract class AbstractMultiInstanceLoopCharacteristicsBuilder extends AbstractBaseElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        MultiInstanceLoopCharacteristicsInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function sequential(): AbstractMultiInstanceLoopCharacteristicsBuilder
    {
        $this->element->setSequential(true);
        return $this->myself;
    }

    public function parallel(): AbstractMultiInstanceLoopCharacteristicsBuilder
    {
        $this->element->setSequential(false);
        return $this->myself;
    }

    public function cardinality(string $expression): AbstractMultiInstanceLoopCharacteristicsBuilder
    {
        $cardinality = $this->getCreateSingleChild(LoopCardinalityInterface::class);
        $cardinality->setTextContent($expression);
        return $this->myself;
    }

    public function completionCondition(string $expression): AbstractMultiInstanceLoopCharacteristicsBuilder
    {
        $condition = $this->getCreateSingleChild(CompletionConditionInterface::class);
        $condition->setTextContent($expression);
        return $this->myself;
    }

    public function collection(string $expression): AbstractMultiInstanceLoopCharacteristicsBuilder
    {
        $this->element->setCollection($expression);
        return $this->myself;
    }

    public function elementVariable(string $variableName): AbstractMultiInstanceLoopCharacteristicsBuilder
    {
        $this->element->setElementVariable($variableName);
        return $this->myself;
    }

    public function asyncBefore(): AbstractMultiInstanceLoopCharacteristicsBuilder
    {
        $this->element->setAsyncBefore(true);
        return $this->myself;
    }

    public function asyncAfter(): AbstractMultiInstanceLoopCharacteristicsBuilder
    {
        $this->element->setAsyncAfter(true);
        return $this->myself;
    }

    public function multiInstanceDone(): AbstractActivityBuilder
    {
        return $this->element->getParentElement()->builder();
    }
}
