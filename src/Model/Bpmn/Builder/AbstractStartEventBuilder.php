<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\Exception\BpmnModelException;
use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\Extension\{
    FormDataInterface,
    FormFieldInterface
};
use Jabe\Model\Bpmn\Instance\{
    ErrorEventDefinitionInterface,
    EscalationEventDefinitionInterface,
    StartEventInterface
};

abstract class AbstractStartEventBuilder extends AbstractCatchEventBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        StartEventInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function formHandlerClass(string $className): AbstractStartEventBuilder
    {
        $this->element->setFormHandlerClass($className);
        return $this;
    }

    public function formKey(string $formKey): AbstractStartEventBuilder
    {
        $this->element->setFormKey($formKey);
        return $this;
    }

    public function initiator(string $initiator): AbstractStartEventBuilder
    {
        $this->element->setInitiator($initiator);
        return $this;
    }

    public function formField(): StartEventFormFieldBuilder
    {
        $formData = $this->getCreateSingleExtensionElement(FormDataInterface::class);
        $formField = $this->createChild($formData, FormFieldInterface::class);
        return new StartEventFormFieldBuilder($this->modelInstance, $this->element, $formField);
    }

    public function error(?string $errorCode = null, ?string $errorMessage = null): AbstractStartEventBuilder
    {
        if ($errorCode === null) {
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
        if ($id !== null) {
            $errorEventDefinition->setId($id);
        }
        $this->element->addEventDefinition($errorEventDefinition);
        return new ErrorEventDefinitionBuilder($this->modelInstance, $errorEventDefinition);
    }

    public function escalation(?string $escalationCode = null): AbstractStartEventBuilder
    {
        if ($escalationCode === null) {
            $escalationEventDefinition = $this->createInstance(EscalationEventDefinitionInterface::class);
        } else {
            $escalationEventDefinition = $this->createEscalationEventDefinition($escalationCode);
        }
        $this->element->addEventDefinition($escalationEventDefinition);
        return $this;
    }

    public function compensation(): AbstractStartEventBuilder
    {
        $compensateEventDefinition = $this->createCompensateEventDefinition();
        $this->element->addEventDefinition($compensateEventDefinition);
        return $this;
    }

    public function interrupting(bool $interrupting): AbstractStartEventBuilder
    {
        $this->element->setInterrupting($interrupting);
        return $this;
    }
}
