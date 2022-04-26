<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\Exception\BpmnModelException;
use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\ErrorEventDefinitionInterface;

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
        return $this;
    }

    public function errorMessageVariable(string $errorMessageVariable): AbstractErrorEventDefinitionBuilder
    {
        $this->element->setErrorMessageVariable($errorMessageVariable);
        return $this;
    }

    public function error(string $errorCode, ?string $errorMessage): AbstractErrorEventDefinitionBuilder
    {
        $this->element->setError($this->findErrorForNameAndCode($errorCode, $errorMessage));
        return $this;
    }

    public function errorEventDefinitionDone(): AbstractFlowNodeBuilder
    {
        return $this->element->getParentElement()->builder();
    }
}
