<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\ErrorEventDefinitionInterface;

abstract class AbstractErrorEventDefinitionBuilder extends AbstractRootElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        ErrorEventDefinitionInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function id(string $identifier): AbstractErrorEventDefinitionBuilder
    {
        return parent::id($identifier);
    }

    public function errorCodeVariable(string $errorCodeVariable): AbstractErrorEventDefinitionBuilder
    {
        $this->element->setErrorCodeVariable($errorCodeVariable);
        return $this->myself;
    }

    public function errorMessageVariable(string $errorMessageVariable): AbstractErrorEventDefinitionBuilder
    {
        $this->element->setErrorMessageVariable($errorMessageVariable);
        return $this->myself;
    }

    public function error(string $errorCode, ?string $errorMessage): AbstractErrorEventDefinitionBuilder
    {
        $this->element->setError($this->findErrorForNameAndCode($errorCode, $errorMessage));
        return $this->myself;
    }

    public function errorEventDefinitionDone(): AbstractFlowNodeBuilder
    {
        return $this->element->getParentElement()->builder();
    }
}
